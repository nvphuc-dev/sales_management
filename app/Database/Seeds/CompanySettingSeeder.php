<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Models\CompanySettingModel;
use CodeIgniter\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        $model = model(CompanySettingModel::class);
        if ($model->find(1) !== null) {
            return;
        }

        $model->insert([
            'id'            => 1,
            'shop_name'     => 'Công ty / Cửa hàng',
            'phone'         => '',
            'email'         => '',
            'address_line1' => '',
            'address_line2' => '',
            'tax_code'      => '',
            'website'       => '',
        ]);
    }
}
