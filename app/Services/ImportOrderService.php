<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use CodeIgniter\Database\BaseConnection;

final class ImportOrderService
{
    private BaseConnection $db;

    public function __construct(
        ?BaseConnection $db = null,
        private readonly ?InventoryService $inventory = null,
    ) {
        $this->db = $db ?? db_connect();
    }

    public static function withConnection(BaseConnection $db): self
    {
        return new self($db, InventoryService::withConnection($db));
    }

    private function inventory(): InventoryService
    {
        return $this->inventory ?? InventoryService::withConnection($this->db);
    }

    /**
     * @param array{
     *   code:string,
     *   supplier_id:int,
     *   notes?:string|null,
     *   items:list<array{product_id:int,quantity:int,unit_price:string}>
     * } $payload
     */
    public function createImportOrder(array $payload): int
    {
        if ($payload['code'] === '' || $payload['supplier_id'] < 1) {
            throw new BusinessRuleException('Thiếu mã phiếu hoặc nhà cung cấp.');
        }

        $items = $payload['items'] ?? [];
        if ($items === []) {
            throw new BusinessRuleException('Phiếu nhập phải có ít nhất một dòng hàng.');
        }

        $this->db->transStart();

        $supplier = $this->db->query(
            'SELECT id FROM suppliers WHERE id = ? FOR UPDATE',
            [(int) $payload['supplier_id']],
        )->getRowArray();
        if ($supplier === null) {
            $this->db->transRollback();
            throw new BusinessRuleException('Nhà cung cấp không tồn tại.');
        }

        $total = '0.00';
        $lines = [];
        foreach ($items as $line) {
            $pid = (int) $line['product_id'];
            $qty = (int) $line['quantity'];
            if ($pid < 1 || $qty < 1) {
                $this->db->transRollback();
                throw new BusinessRuleException('Dòng phiếu không hợp lệ.');
            }

            $unit = Money::normalize((string) $line['unit_price']);
            if (! Money::isPositive($unit)) {
                $this->db->transRollback();
                throw new BusinessRuleException('Đơn giá nhập phải > 0.');
            }

            $lineTotal = Money::mul($unit, (string) $qty);
            $total      = Money::add($total, $lineTotal);
            $lines[]    = ['product_id' => $pid, 'quantity' => $qty, 'unit_price' => $unit, 'line_total' => $lineTotal];
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('import_orders')->insert([
            'code'         => $payload['code'],
            'supplier_id'  => (int) $payload['supplier_id'],
            'total_amount' => $total,
            'notes'        => $payload['notes'] ?? null,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);
        $importId = (int) $this->db->insertID();
        if ($importId < 1) {
            $this->db->transRollback();
            throw new BusinessRuleException('Không tạo được phiếu nhập.');
        }

        foreach ($lines as $line) {
            $this->db->table('import_order_items')->insert([
                'import_order_id' => $importId,
                'product_id'      => $line['product_id'],
                'quantity'        => $line['quantity'],
                'unit_price'      => $line['unit_price'],
                'line_total'      => $line['line_total'],
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
            $this->inventory()->updateStock($line['product_id'], $line['quantity'], 'in');
        }

        if ($this->db->transComplete() === false) {
            throw new BusinessRuleException('Giao dịch CSDL thất bại.');
        }

        return $importId;
    }
}
