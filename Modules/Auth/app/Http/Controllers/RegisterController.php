<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\User\Entities\User;
use Modules\User\Services\UserService;

class RegisterController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth::register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'dni' => ['required', 'string', 'size:8', 'unique:users,dni', 'regex:/^[0-9]{8}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
        ], [
            'dni.regex' => 'El DNI debe contener exactamente 8 dígitos numéricos.',
            'dni.size' => 'El DNI debe tener exactamente 8 caracteres.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        try {
            $user = $this->userService->create([
                'dni' => $request->dni,
                'email' => $request->email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'password' => $request->password,
                'phone' => $request->phone,
                'is_active' => true,
            ]);

            $role = \Modules\Auth\Entities\Role::where('slug', 'applicant')->first();
            if (!$role) {
                throw new \Exception('Rol "applicant" no encontrado. ¿Ejecutaste los seeders?');
            }
            $user->syncRoles([$role->id]);

            Auth::login($user);
            return redirect()->route('dashboard')->with('success', 'Registro exitoso. Bienvenido al sistema.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al registrar usuario: ' . $e->getMessage());
        }
    }
}
