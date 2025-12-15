<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\User\Entities\User;
use Modules\User\Services\UserService;
use Modules\Auth\Services\ReniecService;

class RegisterController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected ReniecService $reniecService
    ) {}

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        // Verificar si RENIEC está habilitado
        $reniecEnabled = $this->reniecService->isEnabled();

        Log::info('RegisterController: Mostrando formulario de registro', [
            'reniec_enabled' => $reniecEnabled
        ]);

        return view('auth::register', [
            'reniecEnabled' => $reniecEnabled
        ]);
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        // Reglas de validación base
        $rules = [
            'dni' => ['required', 'string', 'size:8', 'unique:users,dni', 'regex:/^[0-9]{8}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];

        // Si RENIEC está habilitado y requiere código verificador, agregarlo a las reglas
        $reniecEnabled = $this->reniecService->isEnabled();
        $requireVerificationCode = config('auth.reniec.require_verification_code', true);

        if ($reniecEnabled && $requireVerificationCode) {
            $rules['codigo_verificador'] = ['required', 'string', 'size:1'];
        }

        $messages = [
            'dni.regex' => 'El DNI debe contener exactamente 8 dígitos numéricos.',
            'dni.size' => 'El DNI debe tener exactamente 8 caracteres.',
            'dni.unique' => 'Este DNI ya está registrado en el sistema.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'codigo_verificador.required' => 'El código verificador del DNI es obligatorio.',
            'codigo_verificador.size' => 'El código verificador debe ser de 1 carácter.',
        ];

        $request->validate($rules, $messages);

        try {
            // Validar con RENIEC si está habilitado
            if ($reniecEnabled) {
                $dni = $request->dni;
                $codigoVerificador = $request->codigo_verificador;

                Log::info('RegisterController: Validando DNI con RENIEC', [
                    'dni' => $dni
                ]);

                $validacion = $this->reniecService->validarParaRegistro($dni, $codigoVerificador);

                if (!$validacion['valid']) {
                    Log::warning('RegisterController: Validación RENIEC fallida', [
                        'dni' => $dni,
                        'message' => $validacion['message']
                    ]);

                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['dni' => $validacion['message']]);
                }

                Log::info('RegisterController: DNI validado exitosamente con RENIEC', [
                    'dni' => $dni
                ]);

                // Opcional: Verificar que los nombres coincidan con RENIEC
                // Esto es útil para evitar registros con datos incorrectos
                $datosReniec = $validacion['data'];

                // Aquí puedes decidir si quieres forzar los datos de RENIEC
                // o permitir que el usuario los modifique
                // Por ahora, solo validamos que el DNI sea correcto
            }

            // Crear el usuario
            Log::info('RegisterController: Creando usuario', [
                'dni' => $request->dni,
                'email' => $request->email
            ]);

            $user = $this->userService->create([
                'dni' => $request->dni,
                'email' => $request->email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'password' => $request->password,
                'phone' => $request->phone,
                'is_active' => true,
            ]);

            // Asignar rol de postulante
            $role = \Modules\Auth\Entities\Role::where('slug', 'applicant')->first();
            if (!$role) {
                throw new \Exception('Rol "applicant" no encontrado. ¿Ejecutaste los seeders?');
            }
            $user->syncRoles([$role->id]);

            Log::info('RegisterController: Usuario registrado exitosamente', [
                'user_id' => $user->id,
                'dni' => $user->dni
            ]);

            // Iniciar sesión automáticamente
            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Registro exitoso. Bienvenido al sistema.');

        } catch (\Exception $e) {
            Log::error('RegisterController: Error en registro de usuario', [
                'dni' => $request->dni,
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['general' => 'Error al registrar usuario. Por favor, intente nuevamente.']);
        }
    }
}
