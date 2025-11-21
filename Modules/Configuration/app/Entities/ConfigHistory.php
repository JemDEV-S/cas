<?php

namespace Modules\Configuration\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfigHistory extends BaseModel
{
    protected $table = 'config_history';

    protected $fillable = [
        'system_config_id',
        'old_value',
        'new_value',
        'changed_by',
        'changed_at',
        'change_reason',
        'ip_address',
        'metadata',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relación con la configuración
     */
    public function config(): BelongsTo
    {
        return $this->belongsTo(SystemConfig::class, 'system_config_id');
    }

    /**
     * Relación con el usuario que realizó el cambio
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
