<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePingLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INTEGER', 'primary_key' => true, 'auto_increment' => true],
            'empresa_id'  => ['type' => 'INTEGER', 'null' => false],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 20],
            'latency'     => ['type' => 'FLOAT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        
        $this->forge->addKey('empresa_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('ping_logs');
    }

    public function down()
    {
        $this->forge->dropTable('ping_logs');
    }
}
