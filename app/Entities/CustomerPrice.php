<?php

declare(strict_types=1);

namespace App\Entities;

final class CustomerPrice
{
    public function __construct(
        public ?int $id,
        public int $customerId,
        public int $productId,
        public string $customPrice,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) $row['customer_id'],
            (int) $row['product_id'],
            (string) $row['custom_price'],
        );
    }
}
