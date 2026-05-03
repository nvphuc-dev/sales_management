<?php

declare(strict_types=1);

namespace App\Entities;

final class SalesOrder
{
    public const STATUS_PENDING    = 'pending';
    public const STATUS_SHIPPING   = 'shipping';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_CANCELLED  = 'cancelled';

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PAID   = 'paid';

    public function __construct(
        public ?int $id,
        public string $orderCode,
        public int $customerId,
        public ?int $driverId,
        public string $status,
        public string $paymentStatus,
        public string $totalAmount,
        public ?string $deliveryNotes,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) $row['order_code'],
            (int) $row['customer_id'],
            isset($row['driver_id']) ? (int) $row['driver_id'] : null,
            (string) $row['status'],
            (string) $row['payment_status'],
            (string) $row['total_amount'],
            isset($row['delivery_notes']) ? (string) $row['delivery_notes'] : null,
        );
    }
}
