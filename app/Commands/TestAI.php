<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SettingsModel;

class TestAI extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'ai:check-settings';
    protected $description = 'Revisa las configuraciones guardadas de IA.';

    public function run(array $params)
    {
        $settingsModel = new SettingsModel();
        $settings = $settingsModel->getClassSettings('AI');

        CLI::write("--- SETTINGS EN BASE DE DATOS ---", "yellow");
        print_r($settings);

        CLI::write("\n--- PROBANDO GENERACIÓN ---", "cyan");
        $aiService = new \App\Libraries\AIService();
        $summary = $aiService->generateSummary("Prueba", "Error en VM 101", "warning");

        if ($summary) {
            CLI::write("ÉXITO: " . $summary, "green");
        } else {
            CLI::write("ERROR: " . $aiService->getLastError(), "red");
        }
    }
}
