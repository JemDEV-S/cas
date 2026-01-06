<?php

namespace Modules\Application\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationAcademic extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'application_id',
        'institution_name',
        'degree_type',
        'career_field',
        'career_id',
        'is_related_career', // 💎 NUEVO: Es carrera afín
        'related_career_name', // 💎 NUEVO: Nombre de carrera afín
        'degree_title',
        'issue_date',
        'is_verified',
        'verification_notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'is_verified' => 'boolean',
        'is_related_career' => 'boolean', // 💎 NUEVO
    ];

    /**
     * Relación con la postulación
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relación con la carrera académica
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(AcademicCareer::class, 'career_id');
    }

    /**
     * Obtener el nombre del tipo de grado
     */
    public function getDegreeTypeNameAttribute(): string
    {
        return match($this->degree_type) {
            'SECUNDARIA' => 'Educación Secundaria',
            'TECNICO' => 'Técnico',
            'BACHILLER' => 'Bachiller',
            'TITULO' => 'Título Profesional',
            'MAESTRIA' => 'Maestría',
            'DOCTORADO' => 'Doctorado',
            default => $this->degree_type,
        };
    }
}
