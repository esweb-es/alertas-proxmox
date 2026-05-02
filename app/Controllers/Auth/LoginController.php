<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

class LoginController extends BaseController
{
    // ---------------------------------------------------------------------
    // Mostrar la vista de login
    // ---------------------------------------------------------------------
    public function loginView()
    {
        $data['title'] = "Login de usuario";
        
        // Si el usuario ya está logueado, lo mandamos al inicio
        if (auth()->loggedIn()) {
            return redirect()->to(config('Auth')->loginRedirect());
        }

        // Mandamos la vista completa (bloque único)
        echo view('Shield/login', $data);
    }
}
