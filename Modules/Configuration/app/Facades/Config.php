<?php

namespace Modules\Configuration\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade para acceder al ConfigService
 *
 * @method static mixed get(string $key, $default = null)
 * @method static \Modules\Configuration\Entities\SystemConfig set(string $key, $value, ?string $changedBy = null, ?string $reason = null)
 * @method static bool has(string $key)
 * @method static array group(string $groupCode)
 * @method static array all()
 * @method static array updateBatch(array $configs, ?string $changedBy = null, ?string $reason = null)
 * @method static \Modules\Configuration\Entities\SystemConfig reset(string $key, ?string $changedBy = null, ?string $reason = null)
 * @method static void clearCache(?string $key = null)
 * @method static \Illuminate\Support\Collection getHistory(string $key, int $limit = 50)
 *
 * @see \Modules\Configuration\Services\ConfigService
 */
class Config extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Modules\Configuration\Services\ConfigService::class;
    }
}
