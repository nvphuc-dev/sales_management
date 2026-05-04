<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProductModel;

final class ProductCatalogService
{
    public function __construct(
        private readonly ProductModel $products,
    ) {
    }

    public static function make(): self
    {
        return new self(model(ProductModel::class));
    }

    /**
     * @return array{data: list<array<string, mixed>>, pager: \CodeIgniter\Pager\Pager}
     */
    public function paginate(int $page = 1, int $perPage = 20, ?string $search = null): array
    {
        if ($search !== null && $search !== '') {
            $this->products->groupStart()
                ->like('name', $search)
                ->orLike('sku', $search)
                ->groupEnd();
        }

        $this->products->orderBy('display_order', 'ASC')->orderBy('id', 'DESC');
        $data  = $this->products->paginate($perPage, 'default', $page);
        $pager = $this->products->pager;

        return ['data' => $data, 'pager' => $pager];
    }

    public function find(int $id): ?array
    {
        $row = $this->products->find($id);

        return $row !== null ? (array) $row : null;
    }

    public function create(array $data): int
    {
        $this->products->insert($data);

        return (int) $this->products->getInsertID();
    }

    public function update(int $id, array $data): bool
    {
        return $this->products->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->products->delete($id, true) !== false;
    }

    /**
     * @return list<array{id:int,text:string}>
     */
    public function searchForSelect2(string $term, int $limit = 30): array
    {
        $builder = $this->products->builder()
            ->select('id, name, sku, selling_price')
            ->where('status', 'active');

        if ($term !== '') {
            $builder->groupStart()
                ->like('name', $term)
                ->orLike('sku', $term)
                ->groupEnd();
        }

        $rows = $builder
            ->orderBy('name', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id'   => (int) $r['id'],
                'text' => $r['name'] . ' (' . $r['sku'] . ') — ' . $r['selling_price'],
            ];
        }

        return $out;
    }
}
