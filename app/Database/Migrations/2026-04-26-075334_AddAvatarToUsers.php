<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAvatarToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'avatar' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'username'
            ],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'avatar');
    }
}
