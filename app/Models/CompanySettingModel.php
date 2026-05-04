<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CompanySettingModel extends Model
{
    protected $table            = 'company_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'id',
        'shop_name',
        'phone',
        'email',
        'address_line1',
        'address_line2',
        'tax_code',
        'website',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    private const DEFAULT_ROW_ID = 1;

    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'shop_name'     => '',
            'phone'         => '',
            'email'         => '',
            'address_line1' => '',
            'address_line2' => '',
            'tax_code'      => '',
            'website'       => '',
        ];
    }

    /**
     * Bản ghi duy nhất (id=1) dùng cho in đơn / hiển thị.
     *
     * @return array<string, string>
     */
    public function getSingletonRow(): array
    {
        $row = $this->find(self::DEFAULT_ROW_ID);

        return $row === null ? self::defaults() : array_merge(self::defaults(), $row);
    }

    public function saveSingleton(array $data): bool
    {
        unset($data['id']);
        if ($this->find(self::DEFAULT_ROW_ID) === null) {
            $data['id'] = self::DEFAULT_ROW_ID;

            return $this->insert($data) !== false;
        }

        return $this->update(self::DEFAULT_ROW_ID, $data) !== false;
    }
}
