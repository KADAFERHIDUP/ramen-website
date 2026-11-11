<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenuItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'slug'         => ['type' => 'VARCHAR', 'constraint' => 80],
            'kind'         => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'ramen'],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'description'  => ['type' => 'TEXT', 'null' => true],
            'price'        => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'categories'   => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'spicy'        => ['type' => 'TINYINT', 'null' => true],
            'image'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'model'        => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'badge'        => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'signature'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'show_in_menu' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'is_available' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'is_active'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort'         => ['type' => 'INT', 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('menu_items');
    }

    public function down()
    {
        $this->forge->dropTable('menu_items');
    }
}
