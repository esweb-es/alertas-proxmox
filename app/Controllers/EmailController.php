<?php

namespace App\Controllers;

use App\Models\SettingsModel;

class EmailController extends BaseController
{
    protected $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
    }

    // ---------------------------------------------------------------------
    // Mostrar formulario de configuración
    // ---------------------------------------------------------------------
    public function index()
    {
        $emailSettings = $this->settingsModel->getClassSettings('Email');

        $data = [
            'title'    => 'Configuración de Email',
            'settings' => $emailSettings
        ];

        return view('template/header', $data)
             . view('email/index', $data)
             . view('template/footer');
    }

    // ---------------------------------------------------------------------
    // Guardar configuración
    // ---------------------------------------------------------------------
    public function store()
    {
        $rules = [
            'fromEmail' => 'required|valid_email',
            'fromName'  => 'required',
            'SMTPHost'  => 'required',
            'SMTPPort'  => 'required|numeric',
            'SMTPUser'  => 'required',
            'SMTPPass'  => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $fields = [
            'protocol', 'SMTPHost', 'SMTPUser', 'SMTPPass', 
            'SMTPPort', 'SMTPCrypto', 'mailType', 'fromEmail', 'fromName'
        ];

        foreach ($fields as $field) {
            $value = $this->request->getPost($field);
            $this->settingsModel->setSetting('Email', $field, $value);
        }

        return redirect()->to('email')->with('message', 'Configuración de email actualizada correctamente.');
    }

    // ---------------------------------------------------------------------
    // Enviar correo de prueba
    // ---------------------------------------------------------------------
    public function test()
    {
        $email = \Config\Services::email();
        $emailSettings = $this->settingsModel->getClassSettings('Email');

        // Si viene por POST, usamos los datos del formulario (para probar sin guardar)
        // Si no, usamos los de la base de datos
        $config = [
            'protocol'   => $this->request->getPost('protocol') ?? ($emailSettings['protocol'] ?? 'smtp'),
            'SMTPHost'   => $this->request->getPost('SMTPHost') ?? ($emailSettings['SMTPHost'] ?? ''),
            'SMTPUser'   => $this->request->getPost('SMTPUser') ?? ($emailSettings['SMTPUser'] ?? ''),
            'SMTPPass'   => $this->request->getPost('SMTPPass') ?? ($emailSettings['SMTPPass'] ?? ''),
            'SMTPPort'   => (int) ($this->request->getPost('SMTPPort') ?? ($emailSettings['SMTPPort'] ?? 587)),
            'SMTPCrypto' => $this->request->getPost('SMTPCrypto') ?? ($emailSettings['SMTPCrypto'] ?? 'tls'),
            'SMTPTimeout' => 30,
            'mailType'   => $this->request->getPost('mailType') ?? ($emailSettings['mailType'] ?? 'html'),
            'charset'    => 'utf-8',
            'newline'    => "\r\n",
            'CRLF'       => "\r\n"
        ];

        $fromEmail = $this->request->getPost('fromEmail') ?? ($emailSettings['fromEmail'] ?? '');
        $fromName  = $this->request->getPost('fromName') ?? ($emailSettings['fromName'] ?? 'Proxmox Alert');

        if (empty($config['SMTPHost']) || empty($fromEmail)) {
            return redirect()->back()->withInput()->with('error', 'Debe rellenar los datos del servidor para realizar una prueba.');
        }

        $email->initialize($config);

        $email->setFrom($fromEmail, $fromName);
        $email->setTo(auth()->user()->email);
        $email->setSubject('Prueba de Configuración - Proxmox Alert');
        $email->setMessage('<h1>¡Prueba Exitosa!</h1><p>Si has recibido este correo, tu configuración SMTP en Proxmox Alert funciona correctamente.</p><p>Datos usados: ' . $config['SMTPHost'] . '</p>');

        if ($email->send()) {
            return redirect()->to('email')->with('message', 'Correo de prueba enviado correctamente a ' . auth()->user()->email);
        } else {
            return redirect()->to('email')->withInput()->with('error', 'Error al enviar el correo: ' . $email->printDebugger());
        }
    }
}
