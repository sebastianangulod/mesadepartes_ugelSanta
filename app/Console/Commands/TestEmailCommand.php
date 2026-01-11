<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba el envío de correos electrónicos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('El email proporcionado no es válido.');
            return 1;
        }

        $this->info('=== Prueba de Configuración de Email ===');
        $this->newLine();

        // Mostrar configuración actual
        $this->info('Configuración actual:');
        $this->line('  Mailer: ' . config('mail.default'));
        $this->line('  Host: ' . config('mail.mailers.smtp.host'));
        $this->line('  Port: ' . config('mail.mailers.smtp.port'));
        $this->line('  Encryption: ' . (config('mail.mailers.smtp.encryption') ?: 'null'));
        $this->line('  Username: ' . (config('mail.mailers.smtp.username') ?: 'no configurado'));
        $this->line('  Password: ' . (config('mail.mailers.smtp.password') ? '***' : 'no configurado'));
        $this->line('  From Address: ' . config('mail.from.address'));
        $this->line('  From Name: ' . config('mail.from.name'));
        $this->newLine();

        // Verificar configuración
        $this->info('Verificando configuración...');
        $errors = [];

        if (config('mail.default') === 'log') {
            $this->warn('  ⚠ Mailer está configurado como "log" - los emails se guardarán en logs, no se enviarán.');
        }

        if (empty(config('mail.mailers.smtp.host'))) {
            $errors[] = 'MAIL_HOST no está configurado';
        }

        if (empty(config('mail.mailers.smtp.username'))) {
            $errors[] = 'MAIL_USERNAME no está configurado';
        }

        if (empty(config('mail.mailers.smtp.password'))) {
            $errors[] = 'MAIL_PASSWORD no está configurado';
        }

        if (empty(config('mail.from.address'))) {
            $errors[] = 'MAIL_FROM_ADDRESS no está configurado';
        }

        if (!empty($errors)) {
            $this->error('Errores encontrados:');
            foreach ($errors as $error) {
                $this->line('  - ' . $error);
            }
            $this->newLine();
            return 1;
        }

        $this->info('  ✓ Configuración básica OK');
        $this->newLine();

        // Intentar enviar email de prueba
        $this->info("Enviando email de prueba a: {$email}");
        
        try {
            Mail::raw('Este es un email de prueba del sistema Mesa de Partes UGEL Santa. Si recibes este mensaje, la configuración de correo está funcionando correctamente.', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Prueba de Email - Mesa de Partes UGEL Santa');
            });

            $this->newLine();
            $this->info('✓ Email enviado exitosamente!');
            $this->info('Revisa tu bandeja de entrada (y spam) en: ' . $email);
            $this->newLine();

            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('✗ Error al enviar el email:');
            $this->error('  ' . $e->getMessage());
            $this->newLine();
            
            $this->warn('Detalles del error:');
            $this->line('  Tipo: ' . get_class($e));
            $this->line('  Archivo: ' . $e->getFile());
            $this->line('  Línea: ' . $e->getLine());
            $this->newLine();

            // Sugerencias
            $this->info('Sugerencias para solucionar:');
            $this->line('  1. Verifica que MAIL_HOST, MAIL_PORT y MAIL_ENCRYPTION sean correctos');
            $this->line('  2. Para Gmail, asegúrate de usar una "Contraseña de aplicación"');
            $this->line('  3. Verifica que el firewall permita conexiones salientes en el puerto configurado');
            $this->line('  4. Revisa los logs en storage/logs/laravel.log para más detalles');
            $this->newLine();

            Log::error('Error al enviar email de prueba', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}

