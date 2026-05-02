<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWebhookTokenToEmpresas extends Migration
{
    public function up()
    {
        $fields = [
            'webhook_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'active'
            ],
        ];
        $this->forge->addColumn('empresas', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('empresas', 'webhook_token');
    }
}
