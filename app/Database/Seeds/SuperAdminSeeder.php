<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use App\Models\UserModel;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        $users = new UserModel();

        // Datos del Superadmin
        $username = 'admin';
        $email    = 'admin@demo.com';
        $password = 'admin123'; // Cambiar tras el primer login

        // Verificar si ya existe
        if ($users->where('username', $username)->first()) {
            echo "El usuario Superadmin ya existe.\n";
            return;
        }

        // Crear el usuario
        $user = new User([
            'username' => $username,
            'email'    => $email,
            'password' => $password,
        ]);

        $user->active = 1;

        if ($users->save($user)) {
            // Obtener el ID del usuario recién creado
            $user = $users->findById($users->getInsertID());
            
            // Asignar al grupo superadmin
            $user->addGroup('superadmin');
            
            echo "✓ Superadmin creado con éxito.\n";
            echo "  Usuario: {$username}\n";
            echo "  Password: {$password}\n";
        } else {
            echo "❌ Error al crear el Superadmin.\n";
        }
    }
}
