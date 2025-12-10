<?php

namespace Modules\Application\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationTraining extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'application_id',
        'institution',
        'course_name',
        'academic_hours',
        'start_date',
        'end_date',
        'is_verified',
        'verification_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'academic_hours' => 'integer',
        'is_verified' => 'boolean',
    ];

    /**
     * Relación con la postulación
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
