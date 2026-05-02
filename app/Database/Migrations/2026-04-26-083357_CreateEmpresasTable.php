<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmpresasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INTEGER', 'primary_key' => true, 'auto_increment' => true],
            'nombre'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'logo'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'cif'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'email'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'telefono'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'direccion'   => ['type' => 'TEXT', 'null' => true],
            'active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->createTable('empresas');
    }

    public function down()
    {
        $this->forge->dropTable('empresas');
    }
}
