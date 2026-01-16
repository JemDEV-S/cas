<?php

namespace Modules\Notification\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Modules\Notification\Mail\TestMail;

class EmailService
{
    /**
     * Enviar email de prueba usando el mailer por defecto
     */
    public function sendTestEmail(string $to, string $recipientName = 'Usuario'): bool
    {
        try {
            Mail::to($to)->send(new TestMail($recipientName));

            Log::info('Email de prueba enviado exitosamente', [
                'to' => $to,
                'recipient_name' => $recipientName,
                'mailer' => config('mail.default'),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar email de prueba', [
                'to' => $to,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Enviar email de prueba usando un mailer específico
     */
    public function sendTestEmailWithMailer(string $to, string $mailer, string $recipientName = 'Usuario'): bool
    {
        try {
            Mail::mailer($mailer)->to($to)->send(new TestMail($recipientName));

            Log::info('Email de prueba enviado exitosamente', [
                'to' => $to,
                'recipient_name' => $recipientName,
                'mailer' => $mailer,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar email de prueba con mailer específico', [
                'to' => $to,
                'mailer' => $mailer,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Verificar configuración de correo
     */
    public function verifyConfiguration(?string $mailer = null): array
    {
        $mailer = $mailer ?? config('mail.default');

        $config = [
            'mailer' => $mailer,
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];

        if ($mailer === 'smtp' || $mailer === 'failover') {
            $config['host'] = config('mail.mailers.smtp.host');
            $config['port'] = config('mail.mailers.smtp.port');
            $config['encryption'] = config('mail.mailers.smtp.encryption');
            $config['username'] = config('mail.mailers.smtp.username');
        }

        if ($mailer === 'sendmail') {
            $config['path'] = config('mail.mailers.sendmail.path');
        }

        if ($mailer === 'failover') {
            $config['failover_mailers'] = config('mail.mailers.failover.mailers');
        }

        return $config;
    }

    /**
     * Obtener lista de mailers disponibles
     */
    public function getAvailableMailers(): array
    {
        return ['smtp', 'sendmail', 'failover', 'log'];
    }
}
