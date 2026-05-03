<?php

declare(strict_types=1);

namespace App\Entities;

final class Driver
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_BUSY      = 'busy';

    public function __construct(
        public ?int $id,
        public string $name,
        public string $licensePlate,
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
            (string) $row['license_plate'],
            (string) $row['status'],
        );
    }
}
