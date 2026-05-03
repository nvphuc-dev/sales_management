<?php

declare(strict_types=1);

namespace App\Entities;

final class Product
{
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public function __construct(
        public ?int $id,
        public string $name,
        public string $sku,
        public string $purchasePrice,
        public string $sellingPrice,
        public int $stockQuantity,
        public int $displayOrder,
        public string $status,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) $row['name'],
            (string) $row['sku'],
            (string) $row['purchase_price'],
            (string) $row['selling_price'],
            (int) $row['stock_quantity'],
            (int) $row['display_order'],
            (string) $row['status'],
        );
    }
}
