<?php

declare(strict_types=1);

namespace App\Entities;

final class Supplier
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $contactInfo,
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
            isset($row['contact_info']) ? (string) $row['contact_info'] : null,
        );
    }
}
