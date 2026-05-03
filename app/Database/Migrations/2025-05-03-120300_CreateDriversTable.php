<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDriversTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
            ],
            'license_plate' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['available', 'busy'],
                'default'    => 'available',
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
        $this->forge->addKey('status');
        $this->forge->createTable('drivers');
    }

    public function down(): void
    {
        $this->forge->dropTable('drivers');
    }
}
