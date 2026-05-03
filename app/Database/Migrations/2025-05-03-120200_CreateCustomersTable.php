<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomersTable extends Migration
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
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
                'null'       => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'total_purchase' => [
                'type'    => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
            ],
            'total_paid' => [
                'type'    => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
            ],
            'current_debt' => [
                'type'    => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
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
        $this->forge->addKey('phone');
        $this->forge->createTable('customers');
    }

    public function down(): void
    {
        $this->forge->dropTable('customers');
    }
}
