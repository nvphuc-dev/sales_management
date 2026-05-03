<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ImportOrderItemModel;
use App\Models\ImportOrderModel;

final class ImportOrderReadService
{
    public function __construct(
        private readonly ImportOrderModel $imports,
        private readonly ImportOrderItemModel $lines,
    ) {
    }

    public static function make(): self
    {
        return new self(
            model(ImportOrderModel::class),
            model(ImportOrderItemModel::class),
        );
    }

    /**
     * @return array{data: list<array<string, mixed>>, pager: \CodeIgniter\Pager\Pager}
     */
    public function paginate(int $page = 1, int $perPage = 20, ?string $search = null): array
    {
        $this->imports->select('import_orders.*, suppliers.name as supplier_name')
            ->join('suppliers', 'suppliers.id = import_orders.supplier_id');

        if ($search !== null && $search !== '') {
            $this->imports->groupStart()
                ->like('import_orders.code', $search)
                ->orLike('suppliers.name', $search)
                ->groupEnd();
        }

        $this->imports->orderBy('import_orders.id', 'DESC');
        $data  = $this->imports->paginate($perPage, 'default', $page);
        $pager = $this->imports->pager;

        return ['data' => $data, 'pager' => $pager];
    }

    /**
     * @return array{header: array<string, mixed>, items: list<array<string, mixed>>}|null
     */
    public function findWithItems(int $id): ?array
    {
        $header = $this->imports->select('import_orders.*, suppliers.name as supplier_name')
            ->join('suppliers', 'suppliers.id = import_orders.supplier_id')
            ->where('import_orders.id', $id)
            ->first();

        if ($header === null) {
            return null;
        }

        $items = $this->lines->select('import_order_items.*, products.name as product_name, products.sku')
            ->join('products', 'products.id = import_order_items.product_id')
            ->where('import_order_id', $id)
            ->orderBy('import_order_items.id', 'ASC')
            ->findAll();

        return ['header' => (array) $header, 'items' => $items];
    }
}
