<?php

namespace Modules\Application\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class ApplicationExperience extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'application_id',
        'organization',
        'position',
        'start_date',
        'end_date',
        'is_specific',
        'is_public_sector',
        'duration_days',
        'is_verified',
        'verification_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_specific' => 'boolean',
        'is_public_sector' => 'boolean',
        'duration_days' => 'integer',
        'is_verified' => 'boolean',
    ];

    /**
     * Boot del modelo para calcular duración automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($experience) {
            if ($experience->start_date && $experience->end_date) {
                $experience->duration_days = Carbon::parse($experience->start_date)
                    ->diffInDays(Carbon::parse($experience->end_date)) + 1;
            }
        });
    }

    /**
     * Relación con la postulación
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Obtener duración formateada
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_days) {
            return '0 días';
        }

        $years = floor($this->duration_days / 365);
        $months = floor(($this->duration_days % 365) / 30);
        $days = $this->duration_days % 30;

        $parts = [];
        if ($years > 0) $parts[] = "{$years} año" . ($years > 1 ? 's' : '');
        if ($months > 0) $parts[] = "{$months} mes" . ($months > 1 ? 'es' : '');
        if ($days > 0) $parts[] = "{$days} día" . ($days > 1 ? 's' : '');

        return implode(', ', $parts);
    }
}
