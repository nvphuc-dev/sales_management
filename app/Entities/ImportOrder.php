<?php

declare(strict_types=1);

namespace App\Entities;

final class ImportOrder
{
    public function __construct(
        public ?int $id,
        public string $code,
        public int $supplierId,
        public string $totalAmount,
        public ?string $notes,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) $row['code'],
            (int) $row['supplier_id'],
            (string) $row['total_amount'],
            isset($row['notes']) ? (string) $row['notes'] : null,
        );
    }
}
