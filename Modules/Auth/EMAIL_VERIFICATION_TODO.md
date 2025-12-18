# Verificación de Email - Guía de Implementación

## Estado Actual
- El email se marca como verificado automáticamente al registrarse (`email_verified_at` = now())
- Los usuarios pueden acceder inmediatamente después del registro

## Pasos para Implementar Verificación de Email

### 1. Crear Migración para Tokens
```bash
php artisan make:migration create_email_verification_tokens_table
```

**Campos necesarios:**
- `email` (string, indexed)
- `token` (string, unique)
- `expires_at` (timestamp)

### 2. Modificar RegisterController
**Archivo:** `Modules/Auth/app/Http/Controllers/RegisterController.php:60`

**Cambiar:**
```php
'email_verified_at' => now(), // Marcar email como verificado
```

**Por:**
```php
'email_verified_at' => null, // Email sin verificar
```

### 3. Crear EmailVerificationController
**Acciones necesarias:**
- `sendVerificationEmail()` - Enviar email con token
- `verify($token)` - Verificar token y marcar email como verificado
- `resend()` - Reenviar email de verificación

### 4. Crear Notification
```bash
php artisan make:notification VerifyEmailNotification
```

**Contenido del email:**
- Link con token único
- Expiración: 24 horas
- Instrucciones claras

### 5. Middleware de Verificación
**Crear:** `EnsureEmailIsVerified` middleware

**Proteger rutas:**
- Dashboard
- Postulaciones
- Perfil

### 6. Vistas Necesarias
- `email-verification-sent.blade.php` - Confirmación de envío
- `email-verified.blade.php` - Email verificado exitosamente
- `email-verification-expired.blade.php` - Token expirado

### 7. Rutas a Agregar
```php
Route::get('/email/verify/{token}', [EmailVerificationController::class, 'verify']);
Route::post('/email/resend', [EmailVerificationController::class, 'resend']);
```

### 8. Actualizar RegisterController
Después de crear usuario, enviar email:
```php
$user->notify(new VerifyEmailNotification($token));

return redirect()->route('verification.notice')
    ->with('success', 'Te hemos enviado un email de verificación.');
```

## Configuración Requerida
- **SMTP** configurado en `.env`
- **MAIL_FROM_ADDRESS** y **MAIL_FROM_NAME**
- Cola de emails (opcional pero recomendado)

## Seguridad
- Tokens únicos generados con `Str::random(64)`
- Expiración de 24 horas
- Hash del token en base de datos
- Rate limiting en reenvío de emails

## Nota Importante
Al implementar, actualizar también la lógica de login para verificar `email_verified_at` antes de permitir acceso.
