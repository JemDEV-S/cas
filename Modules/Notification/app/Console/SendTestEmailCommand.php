<?php

namespace Modules\Notification\Console;

use Illuminate\Console\Command;
use Modules\Notification\Services\EmailService;

class SendTestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notification:test-email
                            {email : Correo electrónico del destinatario}
                            {--name= : Nombre del destinatario (opcional)}
                            {--mailer= : Mailer a usar (smtp, sendmail, failover)}';

    /**
     * The console command description.
     */
    protected $description = 'Enviar un correo electrónico de prueba para verificar la configuración de email';

    public function __construct(
        protected EmailService $emailService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->option('name') ?? 'Usuario';
        $mailer = $this->option('mailer');

        $this->info('');
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║     PRUEBA DE CONFIGURACIÓN DE CORREO ELECTRÓNICO          ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->info('');

        // Mostrar configuración actual
        $config = $this->emailService->verifyConfiguration($mailer);
        $this->info('📧 Configuración actual:');

        $tableData = [
            ['Mailer', $config['mailer']],
            ['Remitente', $config['from_address']],
            ['Nombre Remitente', $config['from_name']],
        ];

        if (isset($config['host'])) {
            $tableData[] = ['Host', $config['host']];
            $tableData[] = ['Puerto', $config['port']];
            $tableData[] = ['Encriptación', $config['encryption'] ?? 'Ninguna'];
            $tableData[] = ['Usuario', $this->maskEmail($config['username'] ?? null)];
        }

        if (isset($config['path'])) {
            $tableData[] = ['Path Sendmail', $config['path']];
        }

        if (isset($config['failover_mailers'])) {
            $tableData[] = ['Failover Mailers', implode(' → ', $config['failover_mailers'])];
        }

        $this->table(['Parámetro', 'Valor'], $tableData);

        $this->info('');
        $this->info("📤 Enviando correo de prueba a: {$email}");
        $this->info("👤 Nombre del destinatario: {$name}");
        if ($mailer) {
            $this->info("📬 Usando mailer: {$mailer}");
        }
        $this->info('');

        $this->output->write('   Enviando');

        try {
            // Animación simple de carga
            for ($i = 0; $i < 3; $i++) {
                $this->output->write('.');
                sleep(1);
            }

            if ($mailer) {
                $this->emailService->sendTestEmailWithMailer($email, $mailer, $name);
            } else {
                $this->emailService->sendTestEmail($email, $name);
            }

            $this->info('');
            $this->info('');
            $this->info('╔════════════════════════════════════════════════════════════╗');
            $this->info('║  ✅ CORREO ENVIADO EXITOSAMENTE                            ║');
            $this->info('╚════════════════════════════════════════════════════════════╝');
            $this->info('');
            $this->info("   Revisa la bandeja de entrada de: {$email}");
            $this->info("   (También verifica la carpeta de spam/correo no deseado)");
            $this->info('');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->info('');
            $this->info('');
            $this->error('╔════════════════════════════════════════════════════════════╗');
            $this->error('║  ❌ ERROR AL ENVIAR EL CORREO                              ║');
            $this->error('╚════════════════════════════════════════════════════════════╝');
            $this->error('');
            $this->error('   Mensaje de error:');
            $this->error("   {$e->getMessage()}");
            $this->info('');
            $this->info('   Posibles soluciones:');
            $this->info('   1. Verifica las credenciales en el archivo .env');
            $this->info('   2. Prueba con otro mailer: --mailer=sendmail');
            $this->info('   3. Usa failover para intentar ambos: --mailer=failover');
            $this->info('   4. Revisa los logs en storage/logs/laravel.log');
            $this->info('');

            return self::FAILURE;
        }
    }

    /**
     * Enmascarar email para mostrar en consola
     */
    private function maskEmail(?string $email): string
    {
        if (empty($email)) {
            return 'No configurado';
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***';
        }

        $local = $parts[0];
        $domain = $parts[1];

        $maskedLocal = substr($local, 0, 3) . str_repeat('*', max(0, strlen($local) - 3));

        return $maskedLocal . '@' . $domain;
    }
}
