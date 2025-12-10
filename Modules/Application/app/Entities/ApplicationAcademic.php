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
        'degree_title',
        'issue_date',
        'is_verified',
        'verification_notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
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
