<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use CodeIgniter\Database\BaseConnection;

final class DriverService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public static function withConnection(BaseConnection $db): self
    {
        return new self($db);
    }

    public function lockDriver(int $driverId): array
    {
        $row = $this->db->query(
            'SELECT id, status FROM drivers WHERE id = ? FOR UPDATE',
            [$driverId],
        )->getRowArray();

        if ($row === null) {
            throw new BusinessRuleException('Tài xế không tồn tại.');
        }

        return $row;
    }

    public function assertAvailable(int $driverId): void
    {
        $row = $this->lockDriver($driverId);
        if ($row['status'] !== 'available') {
            throw new BusinessRuleException('Tài xế không ở trạng thái rảnh.');
        }
    }

    public function setBusy(int $driverId): void
    {
        $this->db->table('drivers')->where('id', $driverId)->update([
            'status'     => 'busy',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function setAvailable(int $driverId): void
    {
        $this->db->table('drivers')->where('id', $driverId)->update([
            'status'     => 'available',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
