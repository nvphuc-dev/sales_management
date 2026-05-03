<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrdersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'order_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'customer_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'driver_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'shipping', 'completed', 'cancelled'],
                'default'    => 'pending',
            ],
            'payment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['unpaid', 'paid'],
                'default'    => 'unpaid',
            ],
            'total_amount' => [
                'type'    => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
            ],
            'delivery_notes' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addUniqueKey('order_code');
        $this->forge->addKey('status');
        $this->forge->addKey('payment_status');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('driver_id', 'drivers', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('orders');
    }

    public function down(): void
    {
        $this->forge->dropTable('orders');
    }
}
