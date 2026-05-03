<?php

declare(strict_types=1);

namespace App\Entities;

final class Transaction
{
    public const TYPE_PAYMENT_IN = 'payment_in';

    public function __construct(
        public ?int $id,
        public int $orderId,
        public int $customerId,
        public string $amount,
        public string $type,
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
            (int) $row['customer_id'],
            (string) $row['amount'],
            (string) $row['type'],
        );
    }
}
