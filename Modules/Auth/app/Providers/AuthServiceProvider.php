<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Modules\User\Entities\User;

class AuthServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Auth';

    protected string $nameLower = 'auth';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
        
        // Registrar Gate para el sistema de permisos
        $this->registerPermissionGate();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        // Registrar servicios
        $this->app->singleton(\Modules\Auth\Services\AuthService::class);
        $this->app->singleton(\Modules\Auth\Services\RoleService::class);
        $this->app->singleton(\Modules\Auth\Services\PermissionService::class);

        // Registrar repositorios
        $this->app->singleton(\Modules\Auth\Repositories\RoleRepository::class);
        $this->app->singleton(\Modules\Auth\Repositories\PermissionRepository::class);

        // Registrar servicios de RENIEC
        $this->registerReniecServices();

        // Registrar middleware
        $this->app['router']->aliasMiddleware('role', \Modules\Auth\Middleware\CheckRole::class);
        $this->app['router']->aliasMiddleware('permission', \Modules\Auth\Middleware\CheckPermission::class);

        // Registrar policies
        Gate::policy(\Modules\Auth\Entities\Role::class, \Modules\Auth\Policies\RolePolicy::class);
        Gate::policy(\Modules\Auth\Entities\Permission::class, \Modules\Auth\Policies\PermissionPolicy::class);
    }

    /**
     * Registrar servicios de RENIEC con sus dependencias
     */
    protected function registerReniecServices(): void
    {
        // Registrar ReniecApiClient
        $this->app->singleton(\Modules\Auth\Services\Reniec\ReniecApiClient::class, function ($app) {
            return new \Modules\Auth\Services\Reniec\ReniecApiClient(
                apiUrl: config('auth.reniec.api.url'),
                apiToken: config('auth.reniec.api.token'),
                timeout: config('auth.reniec.api.timeout'),
                retryTimes: config('auth.reniec.api.retry.times'),
                retrySleep: config('auth.reniec.api.retry.sleep'),
            );
        });

        // Registrar ReniecValidator
        $this->app->singleton(\Modules\Auth\Services\Reniec\ReniecValidator::class);

        // Registrar ReniecCacheService
        $this->app->singleton(\Modules\Auth\Services\Reniec\ReniecCacheService::class, function ($app) {
            return new \Modules\Auth\Services\Reniec\ReniecCacheService(
                enabled: config('auth.reniec.cache.enabled'),
                ttl: config('auth.reniec.cache.ttl'),
            );
        });

        // Registrar ReniecService principal
        $this->app->singleton(\Modules\Auth\Services\Reniec\ReniecService::class, function ($app) {
            return new \Modules\Auth\Services\Reniec\ReniecService(
                enabled: config('auth.reniec.enabled'),
                apiClient: $app->make(\Modules\Auth\Services\Reniec\ReniecApiClient::class),
                validator: $app->make(\Modules\Auth\Services\Reniec\ReniecValidator::class),
                cache: $app->make(\Modules\Auth\Services\Reniec\ReniecCacheService::class),
            );
        });
    }

    /**
     * Registrar Gate para verificar permisos por slug
     * Esto permite que @can, Gate::allows() y $user->can() funcionen con slugs de permisos
     */
    protected function registerPermissionGate(): void
    {
        Gate::before(function (?User $user, string $ability) {
            // Si no hay usuario autenticado, denegar
            if (!$user) {
                return null;
            }

            // Super-admin tiene acceso a todo
            if ($user->isSuperAdmin()) {
                return true;
            }

            // Si el ability es un slug de permiso (contiene puntos como 'user.view.users')
            if (str_contains($ability, '.')) {
                // Verificar si el usuario tiene el permiso
                return $user->hasPermission($ability) ?: null;
            }

            // Si no es un slug de permiso, continuar con el flujo normal (policies, etc)
            return null;
        });
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', $this->nameLower.'.'.$config_key);

                    // Remove duplicated adjacent segments
                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                    $this->publishes([$file->getPathname() => config_path($config)], 'config');
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace').'\\' . $this->name . '\\View\\Components', $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}