<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImportOrdersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'comment'    => 'Mã phiếu nhập do người dùng đặt',
            ],
            'supplier_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'total_amount' => [
                'type'    => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
            ],
            'notes' => [
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
        $this->forge->addUniqueKey('code');
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('import_orders');
    }

    public function down(): void
    {
        $this->forge->dropTable('import_orders');
    }
}
