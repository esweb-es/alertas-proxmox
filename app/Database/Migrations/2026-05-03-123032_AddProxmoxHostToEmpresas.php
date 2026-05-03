<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProxmoxHostToEmpresas extends Migration
{
    public function up()
    {
        $this->forge->addColumn('empresas', [
            'proxmox_host' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'direccion',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('empresas', 'proxmox_host');
    }
}
