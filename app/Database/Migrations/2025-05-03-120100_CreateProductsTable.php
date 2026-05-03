<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
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
            'sku' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'purchase_price' => [
                'type'    => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
            ],
            'selling_price' => [
                'type'    => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
            ],
            'stock_quantity' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'unsigned'   => false,
            ],
            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
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
        $this->forge->addUniqueKey('sku');
        $this->forge->addKey('status');
        $this->forge->addKey('display_order');
        $this->forge->createTable('products');
    }

    public function down(): void
    {
        $this->forge->dropTable('products');
    }
}
