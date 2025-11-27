<?php

namespace Modules\JobProfile\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class JobProfileServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'JobProfile';

    protected string $nameLower = 'jobprofile';

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
        $this->registerObservers();
        $this->registerPolicies();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }

    /**
     * Register policies
     */
    protected function registerPolicies(): void
    {
        \Illuminate\Support\Facades\Gate::policy(
            \Modules\JobProfile\Entities\JobProfile::class,
            \Modules\JobProfile\Policies\JobProfilePolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \Modules\JobProfile\Entities\PositionCode::class,
            \Modules\JobProfile\Policies\PositionCodePolicy::class
        );
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        $this->registerServices();
        $this->registerRepositories();
    }

    /**
     * Register services
     */
    protected function registerServices(): void
    {
        // Services
        $this->app->singleton(\Modules\JobProfile\Services\JobProfileService::class);
        $this->app->singleton(\Modules\JobProfile\Services\PositionCodeService::class);
        $this->app->singleton(\Modules\JobProfile\Services\CriterionService::class);
        $this->app->singleton(\Modules\JobProfile\Services\VacancyService::class);
        $this->app->singleton(\Modules\JobProfile\Services\ReviewService::class);

        // Bind interfaces
        $this->app->bind(
            \Modules\JobProfile\Services\Contracts\PositionCodeServiceInterface::class,
            \Modules\JobProfile\Services\PositionCodeService::class
        );
    }

    /**
     * Register repositories
     */
    protected function registerRepositories(): void
    {
        $this->app->singleton(\Modules\JobProfile\Repositories\JobProfileRepository::class);
        $this->app->singleton(\Modules\JobProfile\Repositories\Eloquent\PositionCodeRepository::class);

        // Bind interfaces
        $this->app->bind(
            \Modules\JobProfile\Repositories\Contracts\PositionCodeRepositoryInterface::class,
            \Modules\JobProfile\Repositories\Eloquent\PositionCodeRepository::class
        );
    }

    /**
     * Register model observers
     */
    protected function registerObservers(): void
    {
        \Modules\JobProfile\Entities\JobProfile::observe(\Modules\JobProfile\Observers\JobProfileObserver::class);
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
