<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSendEmailToEmpresas extends Migration
{
    public function up()
    {
        $fields = [
            'send_email' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'webhook_token'
            ],
        ];
        $this->forge->addColumn('empresas', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('empresas', 'send_email');
    }
}
