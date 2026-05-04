<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanySettingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'shop_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => '',
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'default'    => '',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
                'default'    => '',
            ],
            'address_line1' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'address_line2' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tax_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'default'    => '',
            ],
            'website' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => '',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('company_settings');
    }

    public function down(): void
    {
        $this->forge->dropTable('company_settings');
    }
}
