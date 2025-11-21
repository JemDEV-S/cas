<?php

namespace Modules\JobProfile\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobProfileRequirement extends BaseModel
{
    use HasUuid;

    protected $fillable = [
        'job_profile_id',
        'category',
        'description',
        'is_mandatory',
        'order',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class);
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_mandatory', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
