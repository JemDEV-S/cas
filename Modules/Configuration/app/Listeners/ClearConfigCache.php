<?php

namespace Modules\Configuration\Listeners;

use Modules\Configuration\Events\ConfigUpdated;
use Modules\Configuration\Services\CacheService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClearConfigCache implements ShouldQueue
{
    public function __construct(
        protected CacheService $cacheService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ConfigUpdated $event): void
    {
        // Limpiar caché específica de la configuración actualizada
        $this->cacheService->forget($event->config->key);

        // Si la configuración pertenece a un grupo, limpiar el grupo también
        if ($event->config->group_id) {
            $groupCode = $event->config->group->code ?? null;
            if ($groupCode) {
                $this->cacheService->flushGroup($groupCode);
            }
        }

        // Log de limpieza de caché
        \Log::info("Cache cleared for configuration: {$event->config->key}", [
            'key' => $event->config->key,
            'changed_by' => $event->changedBy,
        ]);
    }
}
