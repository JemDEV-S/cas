<?php

namespace Modules\JobProfile\Entities;

use Modules\Core\Entities\BaseSoftDelete;
use Modules\Core\Traits\HasUuid;
use Modules\Core\Traits\HasMetadata;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\JobProfile\Enums\VacancyStatusEnum;

class JobProfileVacancy extends BaseSoftDelete
{
    use HasUuid, HasMetadata;

    protected $fillable = [
        'job_profile_id',
        'vacancy_number',
        'code',
        'status',
        'assigned_application_id',
        'declared_vacant_at',
        'declared_vacant_reason',
        'metadata',
    ];

    protected $casts = [
        'vacancy_number' => 'integer',
        'declared_vacant_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'status_label',
        'status_badge',
    ];

    protected $searchable = ['code'];
    protected $sortable = ['code', 'vacancy_number', 'status', 'created_at'];

    // Relaciones
    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class);
    }

    public function assignedApplication(): BelongsTo
    {
        return $this->belongsTo(\Modules\Application\Entities\Application::class, 'assigned_application_id');
    }

    // Scopes
    public function scopeByJobProfile($query, string $jobProfileId)
    {
        return $query->where('job_profile_id', $jobProfileId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeInProcess($query)
    {
        return $query->where('status', 'in_process');
    }

    public function scopeFilled($query)
    {
        return $query->where('status', 'filled');
    }

    public function scopeVacant($query)
    {
        return $query->where('status', 'vacant');
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    // Métodos de estado
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isInProcess(): bool
    {
        return $this->status === 'in_process';
    }

    public function isFilled(): bool
    {
        return $this->status === 'filled';
    }

    public function isVacant(): bool
    {
        return $this->status === 'vacant';
    }

    public function canBeAssigned(): bool
    {
        return in_array($this->status, ['available', 'in_process']);
    }

    public function canBeDeclaredVacant(): bool
    {
        return in_array($this->status, ['available', 'in_process']);
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return VacancyStatusEnum::from($this->status)->label();
    }

    public function getStatusBadgeAttribute(): string
    {
        return VacancyStatusEnum::from($this->status)->badge();
    }

    // Métodos de negocio
    public function markAsInProcess(): bool
    {
        if (!$this->isAvailable()) {
            throw new \LogicException('Solo las vacantes disponibles pueden marcarse como en proceso.');
        }

        return $this->update(['status' => 'in_process']);
    }

    public function assignTo(string $applicationId): bool
    {
        if (!$this->canBeAssigned()) {
            throw new \LogicException('Esta vacante no puede ser asignada en su estado actual.');
        }

        return $this->update([
            'status' => 'filled',
            'assigned_application_id' => $applicationId,
        ]);
    }

    public function declareVacant(string $reason): bool
    {
        if (!$this->canBeDeclaredVacant()) {
            throw new \LogicException('Esta vacante no puede ser declarada desierta en su estado actual.');
        }

        return $this->update([
            'status' => 'vacant',
            'declared_vacant_at' => now(),
            'declared_vacant_reason' => $reason,
        ]);
    }

    public function makeAvailable(): bool
    {
        if ($this->isFilled()) {
            // Solo permite volver a disponible si se desasigna la postulación
            return $this->update([
                'status' => 'available',
                'assigned_application_id' => null,
            ]);
        }

        return $this->update(['status' => 'available']);
    }
}
