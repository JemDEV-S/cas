<?php

namespace Modules\User\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends BaseModel
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'language',
        'timezone',
        'notifications_email',
        'notifications_system',
        'theme',
        'date_format',
        'preferences',
    ];

    protected $casts = [
        'notifications_email' => 'boolean',
        'notifications_system' => 'boolean',
        'preferences' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPreference(string $key, $default = null)
    {
        return data_get($this->preferences, $key, $default);
    }

    public function setPreference(string $key, $value): void
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        $this->preferences = $preferences;
        $this->save();
    }
}
