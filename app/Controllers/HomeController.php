<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    // ---------------------------------------------------------------------
    // Mostrar el Dashboard principal
    // ---------------------------------------------------------------------
    public function index()
    {
        if (! auth()->loggedIn()) {
            return redirect()->to('login');
        }

        $companyModel = new \App\Models\CompanyModel();
        $alertModel  = new \App\Models\AlertModel();

        $empresas = $companyModel->where('active', 1)->findAll();
        
        foreach ($empresas as $empresa) {
            $this->calculateStatus($empresa, $alertModel);
        }

        $data = [
            'title'    => "Escritorio",
            'empresas' => $empresas
        ];

        return view('template/header', $data)
             . view('dashboard', $data)
             . view('template/footer');
    }

    // ---------------------------------------------------------------------
    // Endpoint para actualización por AJAX (Fetch)
    // ---------------------------------------------------------------------
    public function status()
    {
        if (! auth()->loggedIn()) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $companyModel = new \App\Models\CompanyModel();
        $alertModel  = new \App\Models\AlertModel();

        $empresas = $companyModel->where('active', 1)->findAll();
        
        foreach ($empresas as $empresa) {
            $this->calculateStatus($empresa, $alertModel);
        }

        return $this->response->setJSON($empresas);
    }

    // ---------------------------------------------------------------------
    // Lógica compartida para calcular estados y contadores
    // ---------------------------------------------------------------------
    private function calculateStatus(&$empresa, $alertModel)
    {
        $alertasNuevas = $alertModel->where('empresa_id', $empresa->id)
                                     ->where('status', 'new')
                                     ->findAll();
                                     
        $empresa->border_class = ''; // Sin clase extra, usará el gris nativo del tema
        $badgeCount = 0;

        if (count($alertasNuevas) > 0) {
            $hasError = false;
            $hasWarning = false;
            
            foreach ($alertasNuevas as $alerta) {
                $isError = (stripos($alerta->severity, 'error') !== false || stripos($alerta->severity, 'crit') !== false);
                $isWarn  = (stripos($alerta->severity, 'warn') !== false);

                if ($isError) {
                    $hasError = true;
                    $badgeCount++; 
                } elseif ($isWarn) {
                    $hasWarning = true;
                    $badgeCount++; 
                }
            }
            
            if ($hasError) {
                $empresa->border_class = 'border-danger';
            } elseif ($hasWarning) {
                $empresa->border_class = 'border-warning';
            }
        }
        
        $empresa->alert_count = $badgeCount;
        
        // Color para el LED parpadeante (seguimos usando verde para el pulso si todo está OK)
        $pulseColor = 'success';
        if (strpos($empresa->border_class, 'danger') !== false) $pulseColor = 'danger';
        elseif (strpos($empresa->border_class, 'warning') !== false) $pulseColor = 'warning';
        $empresa->pulse_color = $pulseColor;
    }
}
