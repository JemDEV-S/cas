<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Entities\Role;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Services\Reniec\ReniecService;
use Modules\Auth\Exceptions\ReniecException;
use Modules\User\Services\UserService;

class RegisterController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ReniecService $reniecService
    ) {}

    /**
     * Mostrar formulario de registro
     */
    public function showRegistrationForm()
    {
        return view('auth::register', [
            'reniecEnabled' => $this->reniecService->isEnabled()
        ]);
    }

    /**
     * Procesar registro de usuario
     */
    public function register(RegisterRequest $request)
    {
        try {
            // Validar con RENIEC si está habilitado
            if ($request->shouldValidateWithReniec()) {
                $this->validateWithReniec($request);
            }

            // Crear usuario en una transacción
            $user = DB::transaction(function () use ($request) {
                // Preparar datos del usuario, convirtiendo a mayúsculas
                $userData = [
                    'dni' => $request->input('dni'),
                    'email' => strtolower($request->input('email')), // Email en minúsculas
                    'first_name' => strtoupper($request->input('first_name')),
                    'last_name' => strtoupper($request->input('last_name')),
                    'gender' => strtoupper($request->input('gender')),
                    'birth_date' => $request->input('birth_date'),
                    'address' => strtoupper($request->input('address')),
                    'district' => strtoupper($request->input('district')),
                    'province' => strtoupper($request->input('province')),
                    'department' => strtoupper($request->input('department')),
                    'password' => $request->input('password'),
                    'phone' => $request->input('phone'),
                    'is_active' => true,
                    'email_verified_at' => now(), // Marcar email como verificado
                ];

                $user = $this->userService->create($userData);

                // Asignar rol de postulante
                $this->assignApplicantRole($user);

                return $user;
            });

            // Iniciar sesión automáticamente
            Auth::login($user);

            return redirect()
                ->route('dashboard')
                ->with('success', 'Registro exitoso. Bienvenido al sistema.');

        } catch (ReniecException $e) {
            return $this->handleReniecError($e);
        } catch (\Exception $e) {
            Log::error('RegisterController: Error en registro', [
                'dni' => $request->getDni(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['general' => 'Error al registrar usuario. Por favor, intente nuevamente.']);
        }
    }

    /**
     * Validar DNI con RENIEC
     */
    private function validateWithReniec(RegisterRequest $request): void
    {
        $result = $this->reniecService->validateWithCheckDigit(
            $request->getDni(),
            $request->getCodigoVerificador()
        );

        if (!$result->isValid) {
            throw new \Illuminate\Validation\ValidationException(
                validator: validator([], []),
                response: redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['dni' => $result->message])
            );
        }
    }

    /**
     * Asignar rol de postulante al usuario
     */
    private function assignApplicantRole($user): void
    {
        $role = Role::where('slug', 'applicant')->first();

        if (!$role) {
            throw new \RuntimeException('Rol "applicant" no encontrado. ¿Ejecutaste los seeders?');
        }

        $user->syncRoles([$role->id]);
    }

    /**
     * Manejar errores de RENIEC
     */
    private function handleReniecError(ReniecException $e)
    {
        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['dni' => $e->getMessage()]);
    }
}
