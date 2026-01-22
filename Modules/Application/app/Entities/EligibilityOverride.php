<?php

namespace Modules\Application\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Application\Enums\OverrideDecisionEnum;

class EligibilityOverride extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'application_id',
        'original_status',
        'original_reason',
        'new_status',
        'decision',
        'resolution_type',
        'resolution_summary',
        'resolution_detail',
        'resolved_by',
        'resolved_at',
        'metadata',
    ];

    protected $casts = [
        'decision' => OverrideDecisionEnum::class,
        'resolved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relación con la postulación
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relación con el usuario que resolvió
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'resolved_by');
    }

    /**
     * Verificar si el reclamo fue aprobado
     */
    public function isApproved(): bool
    {
        return $this->decision === OverrideDecisionEnum::APPROVED;
    }

    /**
     * Verificar si el reclamo fue rechazado
     */
    public function isRejected(): bool
    {
        return $this->decision === OverrideDecisionEnum::REJECTED;
    }

    /**
     * Obtener etiqueta del tipo de resolución
     */
    public function getResolutionTypeLabelAttribute(): string
    {
        return match($this->resolution_type) {
            'CLAIM' => 'Reclamo',
            'CORRECTION' => 'Corrección de Oficio',
            'OTHER' => 'Otro',
            default => $this->resolution_type,
        };
    }

    /**
     * Obtener etiqueta del estado original
     */
    public function getOriginalStatusLabelAttribute(): string
    {
        return match($this->original_status) {
            'NO_APTO' => 'No Apto',
            'PRESENTADA' => 'Presentada',
            'EN_REVISION' => 'En Revisión',
            default => $this->original_status,
        };
    }

    /**
     * Obtener etiqueta del nuevo estado
     */
    public function getNewStatusLabelAttribute(): string
    {
        return match($this->new_status) {
            'APTO' => 'Apto',
            'NO_APTO' => 'No Apto',
            default => $this->new_status,
        };
    }
}
