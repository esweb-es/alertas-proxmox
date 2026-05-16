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
                                     ->groupStart()
                                         ->like('severity', 'error')
                                         ->orLike('severity', 'crit')
                                         ->orLike('severity', 'emerg')
                                         ->orLike('severity', 'alert')
                                         ->orLike('severity', 'warn')
                                         ->orLike('severity', 'notice')
                                     ->groupEnd()
                                     ->findAll();
                                     
        $empresa->border_class = ''; 
        $badgeCount = count($alertasNuevas);

        if ($badgeCount > 0) {
            $hasError = false;
            $hasWarning = false;
            
            foreach ($alertasNuevas as $alerta) {
                $sev = strtolower($alerta->severity);
                $isError = (strpos($sev, 'error') !== false || strpos($sev, 'crit') !== false || strpos($sev, 'emerg') !== false || strpos($sev, 'alert') !== false);
                $isWarn  = (strpos($sev, 'warn') !== false || strpos($sev, 'notice') !== false);

                if ($isError) {
                    $hasError = true;
                } elseif ($isWarn) {
                    $hasWarning = true;
                }
            }
            
            if ($hasError) {
                $empresa->border_class = 'border-danger';
            } elseif ($hasWarning) {
                $empresa->border_class = 'border-warning';
            }
        }
        
        $empresa->alert_count = $badgeCount;
        
        // Color para el LED parpadeante
        $pulseColor = 'success';
        if (strpos($empresa->border_class, 'danger') !== false) {
            $pulseColor = 'danger';
        } elseif (strpos($empresa->border_class, 'warning') !== false) {
            $pulseColor = 'warning';
        } elseif (strpos($empresa->border_class, 'info') !== false) {
            $pulseColor = 'info';
        }
        
        $empresa->pulse_color = $pulseColor;
    }
}
