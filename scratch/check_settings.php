<?php
define('FCPATH', dirname(__DIR__) . '/public/');
require dirname(__DIR__) . '/vendor/autoload.php';
$app = \Config\Services::codeigniter();
$app->initialize();

$settingsModel = new \App\Models\SettingsModel();
$settings = $settingsModel->getClassSettings('AI');

echo "--- SETTINGS EN BASE DE DATOS ---\n";
print_r($settings);
