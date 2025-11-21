<?php

namespace Modules\JobProfile\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobProfileResponsibility extends BaseModel
{
    use HasUuid;

    protected $fillable = [
        'job_profile_id',
        'description',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
