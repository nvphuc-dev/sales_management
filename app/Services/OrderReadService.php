<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OrderItemModel;
use App\Models\OrderModel;

final class OrderReadService
{
    public function __construct(
        private readonly OrderModel $orders,
        private readonly OrderItemModel $lines,
    ) {
    }

    public static function make(): self
    {
        return new self(
            model(OrderModel::class),
            model(OrderItemModel::class),
        );
    }

    /**
     * @return array{data: list<array<string, mixed>>, pager: \CodeIgniter\Pager\Pager}
     */
    public function paginate(int $page = 1, int $perPage = 20, ?string $search = null): array
    {
        $this->orders->select('orders.*, customers.name as customer_name')
            ->join('customers', 'customers.id = orders.customer_id');

        if ($search !== null && $search !== '') {
            $this->orders->groupStart()
                ->like('orders.order_code', $search)
                ->orLike('customers.name', $search)
                ->groupEnd();
        }

        $this->orders->orderBy('orders.id', 'DESC');
        $data  = $this->orders->paginate($perPage, 'default', $page);
        $pager = $this->orders->pager;

        return ['data' => $data, 'pager' => $pager];
    }

    /**
     * @return array{header: array<string, mixed>, items: list<array<string, mixed>>, paid: string}|null
     */
    public function findWithItems(int $id): ?array
    {
        $header = $this->orders->select('orders.*, customers.name as customer_name, customers.phone as customer_phone')
            ->join('customers', 'customers.id = orders.customer_id')
            ->where('orders.id', $id)
            ->first();

        if ($header === null) {
            return null;
        }

        $items = $this->lines->select('order_items.*, products.name as product_name, products.sku')
            ->join('products', 'products.id = order_items.product_id')
            ->where('order_id', $id)
            ->orderBy('order_items.id', 'ASC')
            ->findAll();

        $paidRow = db_connect()->query(
            'SELECT COALESCE(SUM(amount),0) AS p FROM transactions WHERE order_id = ?',
            [$id],
        )->getRowArray();
        $paid = Money::normalize($paidRow['p'] ?? '0');

        return ['header' => (array) $header, 'items' => $items, 'paid' => $paid];
    }
}
