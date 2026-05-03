<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use CodeIgniter\Database\BaseConnection;

final class PaymentService
{
    private BaseConnection $db;

    public function __construct(
        ?BaseConnection $db = null,
        private readonly ?CustomerService $customers = null,
    ) {
        $this->db = $db ?? db_connect();
    }

    public static function withConnection(BaseConnection $db): self
    {
        return new self($db, CustomerService::withConnection($db));
    }

    private function customerService(): CustomerService
    {
        return $this->customers ?? CustomerService::withConnection($this->db);
    }

    public function getTotalPaidForOrder(int $orderId): string
    {
        $row = $this->db->query(
            'SELECT COALESCE(SUM(amount), 0) AS paid_sum FROM transactions WHERE order_id = ?',
            [$orderId],
        )->getRowArray();

        return Money::normalize($row['paid_sum'] ?? '0');
    }

    public function getRemainingDue(int $orderId, string $orderTotal): string
    {
        return Money::sub(Money::normalize($orderTotal), $this->getTotalPaidForOrder($orderId));
    }

    /**
     * Thu tiền theo đơn: không cho vượt số còn phải thu; đủ thì payment_status = paid.
     */
    public function recordPayment(int $orderId, int $customerId, string $amount): void
    {
        $amount = Money::normalize($amount);
        if (! Money::isPositive($amount)) {
            throw new BusinessRuleException('Số tiền thu phải lớn hơn 0.');
        }

        $this->db->transStart();

        $order = $this->db->query(
            'SELECT id, customer_id, total_amount, payment_status FROM orders WHERE id = ? FOR UPDATE',
            [$orderId],
        )->getRowArray();

        if ($order === null) {
            $this->db->transRollback();
            throw new BusinessRuleException('Đơn hàng không tồn tại.');
        }

        if ((int) $order['customer_id'] !== $customerId) {
            $this->db->transRollback();
            throw new BusinessRuleException('Khách hàng không khớp với đơn hàng.');
        }

        if ($order['payment_status'] === 'paid') {
            $this->db->transRollback();
            throw new BusinessRuleException('Đơn đã thanh toán đủ.');
        }

        $total    = Money::normalize($order['total_amount']);
        $paid     = $this->getTotalPaidForOrder($orderId);
        $after    = Money::add($paid, $amount);

        if (Money::cmp($after, $total) > 0) {
            $this->db->transRollback();
            throw new BusinessRuleException(
                'Thu vượt số còn lại. Còn phải thu: ' . Money::sub($total, $paid),
            );
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('transactions')->insert([
            'order_id'    => $orderId,
            'customer_id' => $customerId,
            'amount'      => $amount,
            'type'        => 'payment_in',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $this->customerService()->applyPayment($customerId, $amount);

        if (Money::cmp($after, $total) === 0) {
            $this->db->table('orders')->where('id', $orderId)->update([
                'payment_status' => 'paid',
                'updated_at'     => $now,
            ]);
        } else {
            $this->db->table('orders')->where('id', $orderId)->update(['updated_at' => $now]);
        }

        if ($this->db->transComplete() === false) {
            throw new BusinessRuleException('Giao dịch CSDL thất bại.');
        }
    }

    /**
     * Xóa các dòng thu tiền của đơn (khi xóa đơn). Gọi trong transaction ngoài.
     */
    public function deletePaymentsForOrder(int $orderId): void
    {
        $this->db->table('transactions')->where('order_id', $orderId)->delete();
    }
}
