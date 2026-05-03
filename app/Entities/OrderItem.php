<?php

declare(strict_types=1);

namespace App\Entities;

final class OrderItem
{
    public function __construct(
        public ?int $id,
        public int $orderId,
        public int $productId,
        public int $quantity,
        public string $unitPrice,
        public string $lineTotal,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) $row['order_id'],
            (int) $row['product_id'],
            (int) $row['quantity'],
            (string) $row['unit_price'],
            (string) $row['line_total'],
        );
    }
}
