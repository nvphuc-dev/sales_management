<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImportOrderItemsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'import_order_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'product_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'unit_price' => [
                'type'    => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'line_total' => [
                'type'    => 'DECIMAL',
                'constraint' => '15,2',
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
        $this->forge->addForeignKey('import_order_id', 'import_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('import_order_items');
    }

    public function down(): void
    {
        $this->forge->dropTable('import_order_items');
    }
}
