<?php

namespace Modules\User\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasUuid;
use Modules\Core\Traits\HasMetadata;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends BaseModel
{
    use HasUuid, HasMetadata;

    protected $fillable = [
        'user_id',
        'birth_date',
        'gender',
        'address',
        'district',
        'province',
        'department',
        'biography',
        'linkedin_url',
        'metadata',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        return trim("{$this->address}, {$this->district}, {$this->province}, {$this->department}");
    }
}
