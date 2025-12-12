<?php

namespace Modules\Jury\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class JuryMember extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'jury_members';

    protected $fillable = [
        'user_id',
        'specialty',
        'years_of_experience',
        'professional_title',
        'bio',
        'is_active',
        'is_available',
        'unavailability_reason',
        'unavailable_from',
        'unavailable_until',
        'training_completed',
        'training_completed_at',
        'training_certificate_path',
        'total_evaluations',
        'total_assignments',
        'average_evaluation_time',
        'consistency_score',
        'average_rating',
        'preferred_areas',
        'max_concurrent_assignments',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'unavailable_from' => 'date',
        'unavailable_until' => 'date',
        'training_completed' => 'boolean',
        'training_completed_at' => 'datetime',
        'total_evaluations' => 'integer',
        'total_assignments' => 'integer',
        'average_evaluation_time' => 'integer',
        'consistency_score' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'years_of_experience' => 'integer',
        'max_concurrent_assignments' => 'integer',
        'preferred_areas' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Relaciones
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(JuryAssignment::class);
    }

    public function conflicts(): HasMany
    {
        return $this->hasMany(JuryConflict::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(JuryHistory::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
            ->where(function ($q) {
                $q->whereNull('unavailable_from')
                  ->orWhere('unavailable_from', '>', now())
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('unavailable_until')
                         ->where('unavailable_until', '<', now());
                  });
            });
    }

    public function scopeTrained($query)
    {
        return $query->where('training_completed', true);
    }

    public function scopeBySpecialty($query, string $specialty)
    {
        return $query->where('specialty', 'like', "%{$specialty}%");
    }

    public function scopeWithWorkload($query)
    {
        return $query->withCount([
            'assignments as active_assignments_count' => function ($q) {
                $q->where('status', 'ACTIVE')
                  ->where('is_active', true);
            }
        ]);
    }

    /**
     * Helper Methods
     */
    public function isFullyQualified(): bool
    {
        return $this->is_active && $this->is_available && $this->training_completed;
    }

    public function canBeAssigned(): bool
    {
        return $this->isFullyQualified() && !$this->isOverloaded();
    }

    public function isOverloaded(): bool
    {
        $activeCount = $this->assignments()
            ->where('status', 'ACTIVE')
            ->where('is_active', true)
            ->count();

        return $activeCount >= $this->max_concurrent_assignments;
    }

    public function getCurrentWorkload(): int
    {
        return $this->assignments()
            ->where('status', 'ACTIVE')
            ->where('is_active', true)
            ->sum('current_evaluations');
    }

    public function getAvailableCapacity(): int
    {
        return max(0, $this->max_concurrent_assignments - $this->getCurrentWorkload());
    }

    public function markAsUnavailable(string $reason, ?\DateTime $from = null, ?\DateTime $until = null): void
    {
        $this->update([
            'is_available' => false,
            'unavailability_reason' => $reason,
            'unavailable_from' => $from ?? now(),
            'unavailable_until' => $until,
        ]);
    }

    public function markAsAvailable(): void
    {
        $this->update([
            'is_available' => true,
            'unavailability_reason' => null,
            'unavailable_from' => null,
            'unavailable_until' => null,
        ]);
    }

    public function completeTraining(?string $certificatePath = null): void
    {
        $this->update([
            'training_completed' => true,
            'training_completed_at' => now(),
            'training_certificate_path' => $certificatePath,
        ]);
    }

    public function updateStatistics(array $stats): void
    {
        $this->update([
            'total_evaluations' => $stats['total_evaluations'] ?? $this->total_evaluations,
            'total_assignments' => $stats['total_assignments'] ?? $this->total_assignments,
            'average_evaluation_time' => $stats['average_evaluation_time'] ?? $this->average_evaluation_time,
            'consistency_score' => $stats['consistency_score'] ?? $this->consistency_score,
            'average_rating' => $stats['average_rating'] ?? $this->average_rating,
        ]);
    }

    /**
     * Attributes
     */
    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? 'N/A';
    }

    public function getEmailAttribute(): string
    {
        return $this->user->email ?? '';
    }

    public function getWorkloadPercentageAttribute(): int
    {
        if ($this->max_concurrent_assignments == 0) {
            return 0;
        }

        return (int) (($this->getCurrentWorkload() / $this->max_concurrent_assignments) * 100);
    }

    public function getStatusBadgeAttribute(): array
    {
        if (!$this->is_active) {
            return ['label' => 'Inactivo', 'color' => 'secondary'];
        }

        if (!$this->is_available) {
            return ['label' => 'No disponible', 'color' => 'warning'];
        }

        if (!$this->training_completed) {
            return ['label' => 'Sin capacitar', 'color' => 'info'];
        }

        if ($this->isOverloaded()) {
            return ['label' => 'Sobrecargado', 'color' => 'danger'];
        }

        return ['label' => 'Disponible', 'color' => 'success'];
    }
}