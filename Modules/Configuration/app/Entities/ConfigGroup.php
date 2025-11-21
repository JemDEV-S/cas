<?php

namespace Modules\Configuration\Entities;

use Modules\Core\Entities\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfigGroup extends BaseModel
{
    protected $table = 'config_groups';

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Relación con las configuraciones del grupo
     */
    public function configs(): HasMany
    {
        return $this->hasMany(SystemConfig::class, 'config_group_id')->orderBy('display_order');
    }

    /**
     * Scope para grupos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
