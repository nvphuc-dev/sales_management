<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use CodeIgniter\Database\BaseConnection;

final class CustomerService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public static function withConnection(BaseConnection $db): self
    {
        return new self($db);
    }

    /**
     * Giá riêng (nếu có), ngược lại null.
     */
    public function getCustomPrice(int $customerId, int $productId): ?string
    {
        $row = $this->db->table('customer_prices')
            ->where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        return Money::normalize($row['custom_price']);
    }

    /**
     * Giá bán áp dụng: customer_prices hoặc giá mặc định sản phẩm.
     */
    public function resolveUnitPrice(int $customerId, int $productId): string
    {
        $custom = $this->getCustomPrice($customerId, $productId);
        if ($custom !== null) {
            return $custom;
        }

        $p = $this->db->table('products')->select('selling_price')->where('id', $productId)->get()->getRowArray();
        if ($p === null) {
            throw new BusinessRuleException('Sản phẩm không tồn tại.');
        }

        return Money::normalize($p['selling_price']);
    }

    /**
     * Ghi nhận bán ghi nợ: tăng tổng mua và dư nợ.
     */
    public function applySaleOnAccount(int $customerId, string $orderTotal): void
    {
        $orderTotal = Money::normalize($orderTotal);
        $this->lockCustomer($customerId);
        $c = $this->getCustomerRow($customerId);
        $this->db->table('customers')->where('id', $customerId)->update([
            'total_purchase' => Money::add(Money::normalize($c['total_purchase']), $orderTotal),
            'current_debt'   => Money::add(Money::normalize($c['current_debt']), $orderTotal),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Đảo ngược tác động lên khách khi hủy/xóa đơn (đã thu paidSum trên đơn).
     */
    public function reverseOrderAccounting(int $customerId, string $orderTotal, string $paidSum): void
    {
        $orderTotal = Money::normalize($orderTotal);
        $paidSum    = Money::normalize($paidSum);
        $unpaid     = Money::sub($orderTotal, $paidSum);

        $this->lockCustomer($customerId);
        $c = $this->getCustomerRow($customerId);

        $this->db->table('customers')->where('id', $customerId)->update([
            'total_purchase' => Money::sub(Money::normalize($c['total_purchase']), $orderTotal),
            'total_paid'     => Money::sub(Money::normalize($c['total_paid']), $paidSum),
            'current_debt'   => Money::sub(Money::normalize($c['current_debt']), $unpaid),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Điều chỉnh dư nợ (delta dương = tăng nợ).
     */
    public function adjustDebt(int $customerId, string $deltaSigned): void
    {
        $delta = Money::normalize($deltaSigned);
        $this->lockCustomer($customerId);
        $c = $this->getCustomerRow($customerId);
        $this->db->table('customers')->where('id', $customerId)->update([
            'current_debt' => Money::add(Money::normalize($c['current_debt']), $delta),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Ghi nhận thu tiền mặt / chuyển khoản (giảm nợ, tăng tổng đã trả).
     */
    public function applyPayment(int $customerId, string $amount): void
    {
        $amount = Money::normalize($amount);
        if (! Money::isPositive($amount)) {
            throw new BusinessRuleException('Số tiền thanh toán phải > 0.');
        }

        $this->lockCustomer($customerId);
        $c = $this->getCustomerRow($customerId);
        $this->db->table('customers')->where('id', $customerId)->update([
            'total_paid'   => Money::add(Money::normalize($c['total_paid']), $amount),
            'current_debt' => Money::sub(Money::normalize($c['current_debt']), $amount),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Chênh lệch tổng đơn khi sửa chi tiết (chỉ nợ + tổng mua, không đụng total_paid).
     */
    public function applyOrderTotalDelta(int $customerId, string $deltaSigned): void
    {
        $delta = Money::normalize($deltaSigned);
        if (Money::isZero($delta)) {
            return;
        }

        $this->lockCustomer($customerId);
        $c = $this->getCustomerRow($customerId);
        $this->db->table('customers')->where('id', $customerId)->update([
            'total_purchase' => Money::add(Money::normalize($c['total_purchase']), $delta),
            'current_debt'   => Money::add(Money::normalize($c['current_debt']), $delta),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function lockCustomer(int $customerId): void
    {
        $ok = $this->db->query('SELECT id FROM customers WHERE id = ? FOR UPDATE', [$customerId])->getRowArray();
        if ($ok === null) {
            throw new BusinessRuleException('Khách hàng không tồn tại.');
        }
    }

    /**
     * @return array<string, string>
     */
    private function getCustomerRow(int $customerId): array
    {
        $c = $this->db->table('customers')->where('id', $customerId)->get()->getRowArray();
        if ($c === null) {
            throw new BusinessRuleException('Khách hàng không tồn tại.');
        }

        return $c;
    }
}
