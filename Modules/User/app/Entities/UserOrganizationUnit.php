<?php

namespace Modules\User\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOrganizationUnit extends BaseModel
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'organization_unit_id',
        'start_date',
        'end_date',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function isCurrent(): bool
    {
        return $this->start_date <= now() &&
            ($this->end_date === null || $this->end_date >= now());
    }
}
