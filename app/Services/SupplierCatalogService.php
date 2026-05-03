<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SupplierModel;

final class SupplierCatalogService
{
    public function __construct(
        private readonly SupplierModel $suppliers,
    ) {
    }

    public static function make(): self
    {
        return new self(model(SupplierModel::class));
    }

    /**
     * @return array{data: list<array<string, mixed>>, pager: \CodeIgniter\Pager\Pager}
     */
    public function paginate(int $page = 1, int $perPage = 20, ?string $search = null): array
    {
        if ($search !== null && $search !== '') {
            $this->suppliers->groupStart()
                ->like('name', $search)
                ->orLike('contact_info', $search)
                ->groupEnd();
        }

        $this->suppliers->orderBy('id', 'DESC');
        $data  = $this->suppliers->paginate($perPage, 'default', $page);
        $pager = $this->suppliers->pager;

        return ['data' => $data, 'pager' => $pager];
    }

    public function find(int $id): ?array
    {
        $row = $this->suppliers->find($id);

        return $row !== null ? (array) $row : null;
    }

    public function create(array $data): int
    {
        $this->suppliers->insert($data);

        return (int) $this->suppliers->getInsertID();
    }

    public function update(int $id, array $data): bool
    {
        return $this->suppliers->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->suppliers->delete($id, true) !== false;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allForDropdown(): array
    {
        return $this->suppliers->orderBy('name', 'ASC')->findAll(500);
    }
}
