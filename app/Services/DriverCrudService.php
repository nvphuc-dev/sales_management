<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DriverModel;

final class DriverCrudService
{
    public function __construct(
        private readonly DriverModel $drivers,
    ) {
    }

    public static function make(): self
    {
        return new self(model(DriverModel::class));
    }

    /**
     * @return array{data: list<array<string, mixed>>, pager: \CodeIgniter\Pager\Pager}
     */
    public function paginate(int $page = 1, int $perPage = 20, ?string $search = null): array
    {
        if ($search !== null && $search !== '') {
            $this->drivers->groupStart()
                ->like('name', $search)
                ->orLike('license_plate', $search)
                ->groupEnd();
        }

        $this->drivers->orderBy('id', 'DESC');
        $data  = $this->drivers->paginate($perPage, 'default', $page);
        $pager = $this->drivers->pager;

        return ['data' => $data, 'pager' => $pager];
    }

    public function find(int $id): ?array
    {
        $row = $this->drivers->find($id);

        return $row !== null ? (array) $row : null;
    }

    public function create(array $data): int
    {
        if (! isset($data['status'])) {
            $data['status'] = 'available';
        }

        $this->drivers->insert($data);

        return (int) $this->drivers->getInsertID();
    }

    public function update(int $id, array $data): bool
    {
        return $this->drivers->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->drivers->delete($id, true) !== false;
    }

    /**
     * @return list<array{id:int,text:string}>
     */
    public function searchForSelect2(string $term, int $limit = 30): array
    {
        $rows = $this->drivers->builder()
            ->select('id, name, license_plate, status')
            ->where('status', 'available')
            ->groupStart()
            ->like('name', $term)
            ->orLike('license_plate', $term)
            ->groupEnd()
            ->orderBy('name', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id'   => (int) $r['id'],
                'text' => $r['name'] . ' — ' . $r['license_plate'],
            ];
        }

        return $out;
    }
}
