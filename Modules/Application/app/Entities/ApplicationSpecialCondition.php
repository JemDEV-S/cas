<?php

namespace Modules\Application\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationSpecialCondition extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'application_id',
        'condition_type',
        'issuing_entity',
        'document_number',
        'issue_date',
        'expiry_date',
        'bonus_percentage',
        'is_verified',
        'verification_notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'bonus_percentage' => 'decimal:2',
        'is_verified' => 'boolean',
    ];

    /**
     * Relación con la postulación
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Obtener el nombre del tipo de condición
     */
    public function getConditionTypeNameAttribute(): string
    {
        return match($this->condition_type) {
            'DISABILITY' => 'Persona con Discapacidad (15%)',
            'MILITARY' => 'Licenciado de las FF.AA. (10%)',
            'ATHLETE_NATIONAL' => 'Deportista Calificado Nacional (10%)',
            'ATHLETE_INTL' => 'Deportista Calificado Internacional (15%)',
            'TERRORISM' => 'Víctima del Terrorismo (10%)',
            default => $this->condition_type,
        };
    }

    /**
     * Verificar si está vigente
     */
    public function isValid(): bool
    {
        if (!$this->expiry_date) {
            return true; // Si no tiene fecha de vencimiento, es válido
        }

        return $this->expiry_date >= now()->toDateString();
    }
}
