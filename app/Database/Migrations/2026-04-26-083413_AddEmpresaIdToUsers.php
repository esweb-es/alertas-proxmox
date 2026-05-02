<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmpresaIdToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'empresa_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'avatar'
            ],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'empresa_id');
    }
}
