<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CustomerModel;

final class CustomerCrudService
{
    public function __construct(
        private readonly CustomerModel $customers,
    ) {
    }

    public static function make(): self
    {
        return new self(model(CustomerModel::class));
    }

    /**
     * @return array{data: list<array<string, mixed>>, pager: \CodeIgniter\Pager\Pager}
     */
    public function paginate(int $page = 1, int $perPage = 20, ?string $search = null): array
    {
        if ($search !== null && $search !== '') {
            $this->customers->groupStart()
                ->like('name', $search)
                ->orLike('phone', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }

        $this->customers->orderBy('id', 'DESC');
        $data  = $this->customers->paginate($perPage, 'default', $page);
        $pager = $this->customers->pager;

        return ['data' => $data, 'pager' => $pager];
    }

    public function find(int $id): ?array
    {
        $row = $this->customers->find($id);

        return $row !== null ? (array) $row : null;
    }

    public function create(array $data): int
    {
        $defaults = [
            'total_purchase' => '0.00',
            'total_paid'     => '0.00',
            'current_debt'   => '0.00',
        ];
        $this->customers->insert(array_merge($defaults, $data));

        return (int) $this->customers->getInsertID();
    }

    public function update(int $id, array $data): bool
    {
        return $this->customers->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->customers->delete($id, true) !== false;
    }

    /**
     * @return list<array{id:int,text:string}>
     */
    public function searchForSelect2(string $term, int $limit = 30): array
    {
        $rows = $this->customers->builder()
            ->select('id, name, phone')
            ->groupStart()
            ->like('name', $term)
            ->orLike('phone', $term)
            ->groupEnd()
            ->orderBy('name', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $phone = $r['phone'] ?? '';
            $out[] = [
                'id'   => (int) $r['id'],
                'text' => $r['name'] . ($phone !== '' && $phone !== null ? ' — ' . $phone : ''),
            ];
        }

        return $out;
    }
}
