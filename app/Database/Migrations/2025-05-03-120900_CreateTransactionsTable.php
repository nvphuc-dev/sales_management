<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'order_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'customer_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'amount' => [
                'type'    => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['payment_in'],
                'default'    => 'payment_in',
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
        $this->forge->addKey('order_id');
        $this->forge->addKey('customer_id');
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('transactions');
    }

    public function down(): void
    {
        $this->forge->dropTable('transactions');
    }
}
