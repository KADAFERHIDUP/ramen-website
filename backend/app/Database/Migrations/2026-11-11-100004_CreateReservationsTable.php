<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReservationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'code'       => ['type' => 'VARCHAR', 'constraint' => 20],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 80],
            'phone'      => ['type' => 'VARCHAR', 'constraint' => 20],
            'date'       => ['type' => 'DATE'],
            'time'       => ['type' => 'VARCHAR', 'constraint' => 5],
            'people'     => ['type' => 'VARCHAR', 'constraint' => 30],
            'area'       => ['type' => 'VARCHAR', 'constraint' => 30],
            'note'       => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 15, 'default' => 'baru'],
            'ip'         => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('date');
        $this->forge->createTable('reservations');
    }

    public function down()
    {
        $this->forge->dropTable('reservations');
    }
}
