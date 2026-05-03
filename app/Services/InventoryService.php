<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use CodeIgniter\Database\BaseConnection;

final class InventoryService
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
     * @param 'in'|'out' $type in = nhập/hoàn kho, out = xuất/bán
     */
    public function updateStock(int $productId, int $quantity, string $type): void
    {
        if ($quantity < 1) {
            throw new BusinessRuleException('Số lượng tồn kho phải ≥ 1.');
        }

        if (! in_array($type, ['in', 'out'], true)) {
            throw new BusinessRuleException('Loại tồn kho không hợp lệ (chỉ in|out).');
        }

        $sql = 'SELECT id, stock_quantity, status FROM products WHERE id = ? FOR UPDATE';
        $row = $this->db->query($sql, [$productId])->getRowArray();
        if ($row === null) {
            throw new BusinessRuleException('Sản phẩm không tồn tại.');
        }

        if ($row['status'] !== 'active' && $type === 'out') {
            throw new BusinessRuleException('Không thể xuất kho sản phẩm không hoạt động.');
        }

        $current = (int) $row['stock_quantity'];
        if ($type === 'out' && $current < $quantity) {
            throw new BusinessRuleException('Không đủ tồn kho.');
        }

        $delta    = $type === 'in' ? $quantity : -$quantity;
        $newStock = $current + $delta;
        $this->db->table('products')->where('id', $productId)->update([
            'stock_quantity' => $newStock,
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
    }
}
