<?php

namespace Modules\Application\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationKnowledge extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'application_id',
        'knowledge_name',
        'proficiency_level',
    ];

    /**
     * Relación con la postulación
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Obtener el nombre del nivel de dominio
     */
    public function getProficiencyLevelNameAttribute(): string
    {
        return match($this->proficiency_level) {
            'BASICO' => 'Básico',
            'INTERMEDIO' => 'Intermedio',
            'AVANZADO' => 'Avanzado',
            default => $this->proficiency_level ?? 'No especificado',
        };
    }
}
