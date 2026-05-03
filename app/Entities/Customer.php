<?php

declare(strict_types=1);

namespace App\Entities;

final class Customer
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public string $totalPurchase,
        public string $totalPaid,
        public string $currentDebt,
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
            isset($row['phone']) ? (string) $row['phone'] : null,
            isset($row['email']) ? (string) $row['email'] : null,
            isset($row['address']) ? (string) $row['address'] : null,
            (string) $row['total_purchase'],
            (string) $row['total_paid'],
            (string) $row['current_debt'],
        );
    }
}
