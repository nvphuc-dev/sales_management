<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $db = $this->db;
        if ((int) $db->table('users')->countAllResults() > 0) {
            return;
        }

        $model = model(UserModel::class);
        $model->insert([
            'username'      => 'admin',
            'password_hash' => password_hash('Admin@123', PASSWORD_DEFAULT),
            'full_name'     => 'Quản trị viên',
            'role'          => UserModel::ROLE_ADMIN,
            'is_active'     => 1,
        ]);
    }
}
