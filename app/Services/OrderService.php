<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\SalesOrder;
use App\Exceptions\BusinessRuleException;
use CodeIgniter\Database\BaseConnection;

final class OrderService
{
    private BaseConnection $db;

    private InventoryService $inventory;

    private CustomerService $customers;

    private DriverService $drivers;

    private PaymentService $payments;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db        = $db ?? db_connect();
        $this->inventory = InventoryService::withConnection($this->db);
        $this->customers = CustomerService::withConnection($this->db);
        $this->drivers   = DriverService::withConnection($this->db);
        $this->payments  = PaymentService::withConnection($this->db);
    }

    /**
     * @param array{
     *   order_code:string,
     *   customer_id:int,
     *   driver_id?:int|null,
     *   delivery_notes?:string|null,
     *   status?:string,
     *   items:list<array{product_id:int,quantity:int}>
     * } $payload
     */
    public function createOrder(array $payload): int
    {
        $customerId = (int) $payload['customer_id'];
        $orderCode   = trim((string) $payload['order_code']);
        if ($orderCode === '' || $customerId < 1) {
            throw new BusinessRuleException('Thiếu mã đơn hoặc khách hàng.');
        }

        $items = $payload['items'] ?? [];
        if ($items === []) {
            throw new BusinessRuleException('Đơn hàng phải có ít nhất một dòng sản phẩm.');
        }

        $driverId = isset($payload['driver_id']) ? (int) $payload['driver_id'] : null;
        if ($driverId !== null && $driverId < 1) {
            $driverId = null;
        }

        $status = $payload['status'] ?? SalesOrder::STATUS_PENDING;
        if (! in_array($status, [
            SalesOrder::STATUS_PENDING,
            SalesOrder::STATUS_SHIPPING,
            SalesOrder::STATUS_COMPLETED,
        ], true)) {
            throw new BusinessRuleException('Trạng thái đơn ban đầu không hợp lệ.');
        }

        $this->db->transStart();

        $this->customers->lockCustomer($customerId);

        if ($driverId !== null) {
            $this->drivers->assertAvailable($driverId);
        }

        $lineTotals = [];
        $orderTotal = '0.00';
        foreach ($items as $line) {
            $pid = (int) $line['product_id'];
            $qty = (int) $line['quantity'];
            if ($pid < 1 || $qty < 1) {
                $this->db->transRollback();
                throw new BusinessRuleException('Dòng đơn không hợp lệ.');
            }

            $p = $this->db->query(
                'SELECT id, status FROM products WHERE id = ? FOR UPDATE',
                [$pid],
            )->getRowArray();
            if ($p === null || $p['status'] !== 'active') {
                $this->db->transRollback();
                throw new BusinessRuleException('Sản phẩm không hợp lệ hoặc ngừng kinh doanh.');
            }

            $unit       = $this->customers->resolveUnitPrice($customerId, $pid);
            $lineTotal  = Money::mul($unit, (string) $qty);
            $orderTotal = Money::add($orderTotal, $lineTotal);
            $lineTotals[] = ['product_id' => $pid, 'quantity' => $qty, 'unit_price' => $unit, 'line_total' => $lineTotal];
        }

        foreach ($lineTotals as $line) {
            $this->inventory->updateStock($line['product_id'], $line['quantity'], 'out');
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('orders')->insert([
            'order_code'     => $orderCode,
            'customer_id'    => $customerId,
            'driver_id'      => $driverId,
            'status'         => $status,
            'payment_status' => SalesOrder::PAYMENT_UNPAID,
            'total_amount'   => $orderTotal,
            'delivery_notes' => $payload['delivery_notes'] ?? null,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);
        $orderId = (int) $this->db->insertID();
        if ($orderId < 1) {
            $this->db->transRollback();
            throw new BusinessRuleException('Không tạo được đơn hàng.');
        }

        foreach ($lineTotals as $line) {
            $this->db->table('order_items')->insert([
                'order_id'    => $orderId,
                'product_id'  => $line['product_id'],
                'quantity'    => $line['quantity'],
                'unit_price'  => $line['unit_price'],
                'line_total'  => $line['line_total'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        $this->customers->applySaleOnAccount($customerId, $orderTotal);

        if ($driverId !== null) {
            $this->drivers->setBusy($driverId);
        }

        if ($this->db->transComplete() === false) {
            throw new BusinessRuleException('Giao dịch CSDL thất bại.');
        }

        return $orderId;
    }

    /**
     * Gán tài xế: chuyển đơn sang đang giao (nếu đang chờ), tài xế sang bận.
     */
    public function assignDriver(int $orderId, int $driverId, ?string $deliveryNotes = null): void
    {
        $this->db->transStart();

        $order = $this->db->query(
            'SELECT id, driver_id, status FROM orders WHERE id = ? FOR UPDATE',
            [$orderId],
        )->getRowArray();
        if ($order === null) {
            $this->db->transRollback();
            throw new BusinessRuleException('Đơn hàng không tồn tại.');
        }

        if (in_array($order['status'], [SalesOrder::STATUS_CANCELLED, SalesOrder::STATUS_COMPLETED], true)) {
            $this->db->transRollback();
            throw new BusinessRuleException('Không gán tài xế cho đơn đã kết thúc.');
        }

        $oldDriver = isset($order['driver_id']) ? (int) $order['driver_id'] : null;
        if ($oldDriver !== null && $oldDriver !== $driverId) {
            $this->drivers->setAvailable($oldDriver);
        }

        if ($oldDriver !== $driverId) {
            $this->drivers->assertAvailable($driverId);
            $this->drivers->setBusy($driverId);
        }

        $update = [
            'driver_id'  => $driverId,
            'status'     => SalesOrder::STATUS_SHIPPING,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($deliveryNotes !== null) {
            $update['delivery_notes'] = $deliveryNotes;
        }

        $this->db->table('orders')->where('id', $orderId)->update($update);

        if ($this->db->transComplete() === false) {
            throw new BusinessRuleException('Giao dịch CSDL thất bại.');
        }
    }

    /**
     * Hoàn tất đơn: tài xế rảnh nếu có.
     */
    public function markCompleted(int $orderId): void
    {
        $this->db->transStart();

        $order = $this->db->query(
            'SELECT id, driver_id, status FROM orders WHERE id = ? FOR UPDATE',
            [$orderId],
        )->getRowArray();
        if ($order === null) {
            $this->db->transRollback();
            throw new BusinessRuleException('Đơn hàng không tồn tại.');
        }

        if ($order['status'] === SalesOrder::STATUS_CANCELLED) {
            $this->db->transRollback();
            throw new BusinessRuleException('Đơn đã hủy.');
        }

        $driverId = isset($order['driver_id']) ? (int) $order['driver_id'] : null;
        if ($driverId !== null) {
            $this->drivers->setAvailable($driverId);
        }

        $this->db->table('orders')->where('id', $orderId)->update([
            'status'     => SalesOrder::STATUS_COMPLETED,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($this->db->transComplete() === false) {
            throw new BusinessRuleException('Giao dịch CSDL thất bại.');
        }
    }

    /**
     * Hủy đơn: hoàn kho, đảo sổ khách, xóa giao dịch thu, tài xế rảnh.
     */
    public function cancelOrder(int $orderId): void
    {
        $this->db->transStart();

        $order = $this->db->query(
            'SELECT id, customer_id, driver_id, status, total_amount FROM orders WHERE id = ? FOR UPDATE',
            [$orderId],
        )->getRowArray();
        if ($order === null) {
            $this->db->transRollback();
            throw new BusinessRuleException('Đơn hàng không tồn tại.');
        }

        if ($order['status'] === SalesOrder::STATUS_CANCELLED) {
            $this->db->transRollback();
            throw new BusinessRuleException('Đơn đã ở trạng thái đã hủy.');
        }

        $paid = $this->payments->getTotalPaidForOrder($orderId);
        $this->restockAllLines($orderId);
        $this->customers->reverseOrderAccounting(
            (int) $order['customer_id'],
            Money::normalize($order['total_amount']),
            $paid,
        );
        $this->payments->deletePaymentsForOrder($orderId);

        $driverId = isset($order['driver_id']) ? (int) $order['driver_id'] : null;
        if ($driverId !== null) {
            $this->drivers->setAvailable($driverId);
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('orders')->where('id', $orderId)->update([
            'status'         => SalesOrder::STATUS_CANCELLED,
            'payment_status' => SalesOrder::PAYMENT_UNPAID,
            'updated_at'     => $now,
        ]);

        if ($this->db->transComplete() === false) {
            throw new BusinessRuleException('Giao dịch CSDL thất bại.');
        }
    }

    /**
     * Xóa đơn: hoàn kho, đảo sổ khách; CASCADE xóa chi tiết và giao dịch thu.
     */
    public function deleteOrder(int $orderId): void
    {
        $this->db->transStart();

        $order = $this->db->query(
            'SELECT id, customer_id, driver_id, status, total_amount FROM orders WHERE id = ? FOR UPDATE',
            [$orderId],
        )->getRowArray();
        if ($order === null) {
            $this->db->transRollback();
            throw new BusinessRuleException('Đơn hàng không tồn tại.');
        }

        $paid = $this->payments->getTotalPaidForOrder($orderId);
        $this->restockAllLines($orderId);
        $this->customers->reverseOrderAccounting(
            (int) $order['customer_id'],
            Money::normalize($order['total_amount']),
            $paid,
        );

        $driverId = isset($order['driver_id']) ? (int) $order['driver_id'] : null;
        if ($driverId !== null) {
            $this->drivers->setAvailable($driverId);
        }

        $this->db->table('orders')->where('id', $orderId)->delete();

        if ($this->db->transComplete() === false) {
            throw new BusinessRuleException('Giao dịch CSDL thất bại.');
        }
    }

    /**
     * Sửa toàn bộ dòng đơn (chưa thu tiền, đơn chờ/đang giao).
     *
     * @param list<array{product_id:int,quantity:int}> $items
     */
    public function updateOrderItems(int $orderId, int $customerId, array $items): void
    {
        if ($items === []) {
            throw new BusinessRuleException('Đơn phải còn ít nhất một dòng sản phẩm.');
        }

        $this->db->transStart();

        $order = $this->db->query(
            'SELECT id, customer_id, status, payment_status, total_amount FROM orders WHERE id = ? FOR UPDATE',
            [$orderId],
        )->getRowArray();
        if ($order === null || (int) $order['customer_id'] !== $customerId) {
            $this->db->transRollback();
            throw new BusinessRuleException('Đơn hàng không hợp lệ.');
        }

        if (in_array($order['status'], [SalesOrder::STATUS_CANCELLED, SalesOrder::STATUS_COMPLETED], true)) {
            $this->db->transRollback();
            throw new BusinessRuleException('Không sửa đơn đã hủy hoặc hoàn thành.');
        }

        if ($order['payment_status'] === SalesOrder::PAYMENT_PAID) {
            $this->db->transRollback();
            throw new BusinessRuleException('Không sửa đơn đã thanh toán đủ.');
        }

        if (! Money::isZero($this->payments->getTotalPaidForOrder($orderId))) {
            $this->db->transRollback();
            throw new BusinessRuleException('Không sửa đơn đã có thu tiền (giai đoạn 2).');
        }

        $this->customers->lockCustomer($customerId);

        $oldLines = $this->db->table('order_items')->where('order_id', $orderId)->get()->getResultArray();
        foreach ($oldLines as $ol) {
            $this->inventory->updateStock((int) $ol['product_id'], (int) $ol['quantity'], 'in');
        }

        $newLines   = [];
        $newTotal   = '0.00';
        foreach ($items as $line) {
            $pid = (int) $line['product_id'];
            $qty = (int) $line['quantity'];
            if ($pid < 1 || $qty < 1) {
                $this->db->transRollback();
                throw new BusinessRuleException('Dòng đơn không hợp lệ.');
            }

            $p = $this->db->query(
                'SELECT id, status FROM products WHERE id = ? FOR UPDATE',
                [$pid],
            )->getRowArray();
            if ($p === null || $p['status'] !== 'active') {
                $this->db->transRollback();
                throw new BusinessRuleException('Sản phẩm không hợp lệ hoặc ngừng kinh doanh.');
            }

            $unit      = $this->customers->resolveUnitPrice($customerId, $pid);
            $lineTotal = Money::mul($unit, (string) $qty);
            $newTotal  = Money::add($newTotal, $lineTotal);
            $newLines[] = ['product_id' => $pid, 'quantity' => $qty, 'unit_price' => $unit, 'line_total' => $lineTotal];
        }

        foreach ($newLines as $line) {
            $this->inventory->updateStock($line['product_id'], $line['quantity'], 'out');
        }

        $this->db->table('order_items')->where('order_id', $orderId)->delete();
        $now = date('Y-m-d H:i:s');
        foreach ($newLines as $line) {
            $this->db->table('order_items')->insert([
                'order_id'   => $orderId,
                'product_id' => $line['product_id'],
                'quantity'   => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'line_total' => $line['line_total'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $oldTotal = Money::normalize($order['total_amount']);
        $delta      = Money::sub($newTotal, $oldTotal);
        $this->db->table('orders')->where('id', $orderId)->update([
            'total_amount' => $newTotal,
            'updated_at'   => $now,
        ]);
        $this->customers->applyOrderTotalDelta($customerId, $delta);

        if ($this->db->transComplete() === false) {
            throw new BusinessRuleException('Giao dịch CSDL thất bại.');
        }
    }

    private function restockAllLines(int $orderId): void
    {
        $lines = $this->db->table('order_items')->where('order_id', $orderId)->get()->getResultArray();
        foreach ($lines as $line) {
            $this->inventory->updateStock((int) $line['product_id'], (int) $line['quantity'], 'in');
        }
    }
}
