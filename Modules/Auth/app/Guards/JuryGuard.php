<?php

namespace Modules\Auth\Guards;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;

class JuryGuard extends SessionGuard
{
    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  array  $credentials
     * @param  bool  $remember
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false)
    {
        // Validar que el usuario tenga el rol de jurado
        $authenticated = parent::attempt($credentials, $remember);

        if ($authenticated && $this->user()) {
            if (!$this->user()->hasRole('JURY')) {
                $this->logout();
                return false;
            }
        }

        return $authenticated;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if (!$user) {
            return false;
        }

        // Verificar que tenga rol de jurado
        if (!$user->hasRole('JURY')) {
            return false;
        }

        return $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(Authenticatable $user, $remember = false)
    {
        // Verificar que tenga rol de jurado antes de login
        if (!$user->hasRole('JURY')) {
            throw new \Modules\Core\Exceptions\UnauthorizedException(
                'Este usuario no tiene permisos de jurado.'
            );
        }

        parent::login($user, $remember);

        // Log de acceso de jurado
        \Log::info('Jury login', [
            'user_id' => $user->id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
