<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrdersTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'code'       => ['type' => 'VARCHAR', 'constraint' => 20],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 80],
            'phone'      => ['type' => 'VARCHAR', 'constraint' => 20],
            'mode'       => ['type' => 'VARCHAR', 'constraint' => 10],
            'address'    => ['type' => 'TEXT', 'null' => true],
            'pay'        => ['type' => 'VARCHAR', 'constraint' => 20],
            'note'       => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true],
            'subtotal'   => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'items'      => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 15, 'default' => 'baru'],
            'ip'         => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('status');
        $this->forge->createTable('orders');

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'order_id'     => ['type' => 'INT', 'unsigned' => true],
            'menu_item_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'unit_price'   => ['type' => 'INT', 'unsigned' => true],
            'qty'          => ['type' => 'INT', 'unsigned' => true],
            'level'        => ['type' => 'TINYINT', 'null' => true],
            'total'        => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('order_items');
    }

    public function down()
    {
        $this->forge->dropTable('order_items');
        $this->forge->dropTable('orders');
    }
}
