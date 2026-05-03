<?php

namespace App\Controllers;

use App\Models\AlertModel;
use App\Models\CompanyModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class MonitoringController extends BaseController
{
    // ---------------------------------------------------------------------
    // Ejecutar chequeo masivo de ping para empresas activas (endpoint cron)
    // ---------------------------------------------------------------------
    public function pingCheck(string $token)
    {
        $expectedToken = (string) env('cron.pingToken');
        if ($expectedToken === '' || ! hash_equals($expectedToken, $token)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $companyModel = new CompanyModel();
        $alertModel = new AlertModel();

        $empresas = $companyModel
            ->where('active', 1)
            ->where('proxmox_host IS NOT NULL')
            ->where('proxmox_host !=', '')
            ->findAll();

        $summary = [
            'total' => count($empresas),
            'ok' => 0,
            'failed' => 0,
            'alerts_created' => 0,
            'alerts_skipped' => 0,
            'alerts_resolved' => 0,
            'checked_at' => date('c'),
        ];

        foreach ($empresas as $empresa) {
            $host = trim((string) ($empresa->proxmox_host ?? ''));
            if ($host === '') {
                continue;
            }

            [$isReachable, $output] = $this->runPing($host);

            if ($isReachable) {
                $summary['ok']++;
                if ($this->resolveOpenPingAlert($alertModel, (int) $empresa->id, $host)) {
                    $summary['alerts_resolved']++;
                }
                continue;
            }

            $summary['failed']++;
            if ($this->shouldCreatePingAlert($alertModel, (int) $empresa->id, $host)) {
                $downAt = date('Y-m-d H:i:s');
                $alertaData = [
                    'empresa_id' => $empresa->id,
                    'title' => 'Proxmox no responde',
                    'message' => "Incidente de conectividad detectado en {$host}. Caída registrada a las {$downAt}.",
                    'severity' => 'error',
                    'hostname' => $host,
                    'timestamp' => date('c'),
                    'raw_data' => json_encode([
                        'source' => 'cron_ping_check',
                        'host' => $host,
                        'down_at' => $downAt,
                        'output' => $output,
                    ], JSON_UNESCAPED_UNICODE),
                    'status' => 'new',
                ];

                if ($alertModel->insert($alertaData)) {
                    $summary['alerts_created']++;

                    // Enviar email si está activado para la empresa
                    if ((int) ($empresa->send_email ?? 0) === 1 && ! empty($empresa->email)) {
                        $this->sendAlertEmail($empresa, $alertaData);
                    }
                }
            } else {
                $summary['alerts_skipped']++;
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'message' => 'Ping check ejecutado',
            'summary' => $summary,
        ]);
    }

    // ---------------------------------------------------------------------
    // Ejecutar ping a un host y devolver estado/salida
    // ---------------------------------------------------------------------
    private function runPing(string $host): array
    {
        $escapedHost = escapeshellarg($host);
        $command = strtoupper(PHP_OS_FAMILY) === 'DARWIN'
            ? "ping -c 1 -W 2000 {$escapedHost} 2>&1"
            : "ping -c 1 -W 2 {$escapedHost} 2>&1";

        $output = [];
        $exitCode = 1;
        exec($command, $output, $exitCode);

        return [$exitCode === 0, implode("\n", $output)];
    }

    // ---------------------------------------------------------------------
    // Evitar alertas duplicadas mientras exista una alerta abierta
    // ---------------------------------------------------------------------
    private function shouldCreatePingAlert(AlertModel $alertModel, int $empresaId, string $host): bool
    {
        $existing = $alertModel
            ->where('empresa_id', $empresaId)
            ->whereIn('title', ['Proxmox no responde', 'Ping Proxmox no responde'])
            ->where('status !=', 'resolved')
            ->first();

        return $existing === null;
    }

    // ---------------------------------------------------------------------
    // Resolver alerta abierta de ping cuando el host vuelve a responder
    // ---------------------------------------------------------------------
    private function resolveOpenPingAlert(AlertModel $alertModel, int $empresaId, string $host): bool
    {
        $db = \Config\Database::connect();
        $builder = $db->table('alertas');
        $recoveredAt = date('Y-m-d H:i:s');

        $builder->where('empresa_id', $empresaId)
            ->whereIn('title', ['Proxmox no responde', 'Ping Proxmox no responde'])
            ->where('status !=', 'resolved');

        $existingCount = $builder->countAllResults(false);
        if ($existingCount < 1) {
            return false;
        }

        $builder->update([
            'status' => 'resolved',
            'message' => "Conectividad restablecida en {$host} a las {$recoveredAt}.",
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    // ---------------------------------------------------------------------
    // Enviar email de alerta usando la configuración SMTP de /email
    // ---------------------------------------------------------------------
    private function sendAlertEmail($empresa, array $alerta): void
    {
        $settingsModel = new \App\Models\SettingsModel();
        $emailSettings = $settingsModel->getClassSettings('Email');

        // Verificar que hay configuración SMTP guardada
        if (empty($emailSettings['SMTPHost']) || empty($emailSettings['fromEmail'])) {
            log_message('error', 'No se puede enviar email de alerta (cron ping): configuración SMTP no establecida en /email');
            return;
        }

        $email = \Config\Services::email();
        $config = [
            'protocol'    => $emailSettings['protocol'] ?? 'smtp',
            'SMTPHost'    => $emailSettings['SMTPHost'] ?? '',
            'SMTPUser'    => $emailSettings['SMTPUser'] ?? '',
            'SMTPPass'    => $emailSettings['SMTPPass'] ?? '',
            'SMTPPort'    => (int) ($emailSettings['SMTPPort'] ?? 587),
            'SMTPCrypto'  => $emailSettings['SMTPCrypto'] ?? 'tls',
            'SMTPTimeout' => 30,
            'mailType'    => $emailSettings['mailType'] ?? 'html',
            'charset'     => 'utf-8',
            'newline'     => "\r\n",
            'CRLF'        => "\r\n",
        ];
        $email->initialize($config);

        $fromEmail = $emailSettings['fromEmail'];
        $fromName  = $emailSettings['fromName'] ?? 'Proxmox Alert';

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($empresa->email);
        $email->setSubject('⚠️ Alerta de Proxmox - ' . $alerta['title']);

        $message = "
            <div style='font-family: sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden;'>
                <div style='background: #5d87ff; padding: 20px; text-align: center; color: white;'>
                    <h2 style='margin: 0;'>Nueva Alerta Crítica</h2>
                </div>
                <div style='padding: 20px;'>
                    <p>Hola <b>{$empresa->nombre}</b>,</p>
                    <p>Se ha detectado un evento importante en tu infraestructura Proxmox:</p>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr><td style='padding: 5px 0; color: #666;'>Título:</td><td style='font-weight: bold;'>{$alerta['title']}</td></tr>
                        <tr><td style='padding: 5px 0; color: #666;'>Nodo/Host:</td><td style='font-weight: bold;'>{$alerta['hostname']}</td></tr>
                        <tr><td style='padding: 5px 0; color: #666;'>Severidad:</td><td style='color: #5d87ff; font-weight: bold;'>{$alerta['severity']}</td></tr>
                        <tr><td style='padding: 5px 0; color: #666;'>Fecha:</td><td>" . date('d/m/Y H:i:s') . "</td></tr>
                    </table>
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                        <p style='margin: 0; color: #333;'><b>Mensaje del sistema:</b></p>
                        <p style='margin: 10px 0 0 0; font-family: monospace; font-size: 13px;'>{$alerta['message']}</p>
                    </div>
                </div>
                <div style='background: #f8f9fa; padding: 15px; text-align: center; color: #999; font-size: 12px;'>
                    Este es un mensaje automático del sistema de alertas de Proxmox.
                </div>
            </div>
        ";

        $email->setMessage($message);
        $email->setAltMessage(strip_tags($message));

        if (! $email->send()) {
            log_message('error', 'Error enviando email de alerta (cron ping) a ' . $empresa->email . ': ' . $email->printDebugger(['headers']));
            return;
        }

        log_message('info', 'Email de alerta (cron ping) enviado correctamente a ' . $empresa->email);
    }
}
