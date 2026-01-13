<?php

namespace Modules\JobProfile\Entities;

use Modules\Core\Entities\BaseSoftDelete;
use Modules\Core\Traits\HasUuid;
use Modules\Core\Traits\HasStatus;
use Modules\Core\Traits\HasMetadata;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Casts\ExperienceDurationCast;

class JobProfile extends BaseSoftDelete
{
    use HasUuid, HasStatus, HasMetadata;

    protected $fillable = [
        'code',
        'job_posting_id',
        'title',
        'profile_name',
        'organizational_unit_id',
        'position_code_id',
        'requesting_unit_id',
        'job_level',
        'contract_type',
        'salary_min',
        'salary_max',
        'description',
        'mission',
        'working_conditions',

        // Requisitos académicos
        'education_level', // Mantener por compatibilidad
        'education_levels', // Nuevo campo para múltiples niveles
        'career_field',
        'title_required',
        'colegiatura_required',

        // Experiencia
        'general_experience_years',
        'specific_experience_years',
        'specific_experience_description',

        // Capacitación, conocimientos, competencias
        'required_courses',
        'knowledge_areas',
        'required_competencies',

        // Funciones del puesto
        'main_functions',

        // Régimen laboral
        'work_regime',
        'justification',

        // Contrato
        'contract_start_date',
        'contract_end_date',
        'work_location',
        'selection_process_name',

        // Vacantes
        'total_vacancies',

        // Estado y revisión
        'status',
        'requested_by',
        'reviewed_by',
        'approved_by',
        'requested_at',
        'reviewed_at',
        'approved_at',
        'review_comments',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'colegiatura_required' => 'boolean',
        'general_experience_years' => ExperienceDurationCast::class,
        'specific_experience_years' => ExperienceDurationCast::class,
        'required_courses' => 'array',
        'knowledge_areas' => 'array',
        'required_competencies' => 'array',
        'main_functions' => 'array',
        'education_levels' => 'array', // Cast para múltiples niveles educativos
        'total_vacancies' => 'integer',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $searchable = ['code', 'title', 'description'];
    protected $sortable = ['code', 'title', 'job_level', 'status', 'created_at'];

    // Relaciones
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(\Modules\JobPosting\Entities\JobPosting::class);
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(\Modules\Organization\Entities\OrganizationalUnit::class);
    }

    public function positionCode(): BelongsTo
    {
        return $this->belongsTo(PositionCode::class);
    }

    public function requestingUnit(): BelongsTo
    {
        return $this->belongsTo(\Modules\Organization\Entities\OrganizationalUnit::class, 'requesting_unit_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class, 'reviewed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class, 'approved_by');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(JobProfileRequirement::class);
    }

    public function responsibilities(): HasMany
    {
        return $this->hasMany(JobProfileResponsibility::class);
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(JobProfileVacancy::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(JobProfileHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Relación con carreras académicas (tabla pivote)
     */
    public function careers(): HasMany
    {
        return $this->hasMany(JobProfileCareer::class, 'job_profile_id');
    }

    /**
     * Todas las postulaciones a este perfil
     */
    public function applications(): HasMany
    {
        return $this->hasMany(\Modules\Application\Entities\Application::class, 'job_profile_id');
    }

    /**
     * Postulaciones ganadoras (con vacante asignada)
     */
    public function winners(): HasMany
    {
        return $this->applications()->winners();
    }

    /**
     * Postulaciones elegibles pendientes de asignación
     */
    public function eligiblePending(): HasMany
    {
        return $this->applications()->pendingAssignment();
    }

    /**
     * Obtener estadísticas de postulaciones
     */
    public function getApplicationStats(): array
    {
        $applications = $this->applications;

        return [
            'total' => $applications->count(),
            'aptos' => $applications->where('is_eligible', true)->count(),
            'no_aptos' => $applications->where('is_eligible', false)->count(),
            'ganadores' => $applications->whereNotNull('assigned_vacancy_id')->count(),
            'pendientes' => $applications->where('is_eligible', true)
                                   ->whereNull('assigned_vacancy_id')
                                   ->count(),
        ];
    }

    /**
     * Obtener IDs de carreras aceptadas (incluyendo equivalencias)
     */
    public function getAcceptedCareerIds(bool $includeEquivalences = true): array
    {
        $careerIds = $this->careers()->pluck('career_id')->toArray();

        if ($includeEquivalences) {
            $allIds = $careerIds;
            foreach ($careerIds as $careerId) {
                $equivalents = \Modules\Application\Entities\AcademicCareerEquivalence::getEquivalentCareerIds($careerId);
                $allIds = array_merge($allIds, $equivalents);
            }
            return array_unique($allIds);
        }

        return $careerIds;
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeInReview($query)
    {
        return $query->where('status', 'in_review');
    }

    public function scopeByJobPosting($query, string $jobPostingId)
    {
        return $query->where('job_posting_id', $jobPostingId);
    }

    public function scopeByPositionCode($query, string $positionCodeId)
    {
        return $query->where('position_code_id', $positionCodeId);
    }

    public function scopeByEducationLevel($query, string $educationLevel)
    {
        return $query->where('education_level', $educationLevel);
    }

    public function scopeByWorkRegime($query, string $workRegime)
    {
        return $query->where('work_regime', $workRegime);
    }

    /**
     * Scope para filtrar solo los perfiles solicitados por el usuario
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }

    /**
     * Scope para filtrar perfiles según permisos del usuario
     */
    public function scopeVisibleFor($query, $user)
    {
        // Si tiene permiso para ver todos, no filtrar
        if ($user->hasPermission('jobprofile.view.profiles')) {
            return $query;
        }

        // Si solo puede ver los propios, filtrar por requested_by
        if ($user->hasPermission('jobprofile.view.own')) {
            return $query->where('requested_by', $user->id);
        }

        // Si no tiene ningún permiso, no mostrar nada
        return $query->whereRaw('1 = 0');
    }

    // Métodos de estado
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInReview(): bool
    {
        return $this->status === 'in_review';
    }

    public function isModificationRequested(): bool
    {
        return $this->status === 'modification_requested';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'modification_requested']);
    }

    public function canSubmitForReview(): bool
    {
        return in_array($this->status, ['draft', 'modification_requested']);
    }

    public function canApprove(): bool
    {
        return $this->status === 'in_review';
    }

    public function canReject(): bool
    {
        return $this->status === 'in_review';
    }

    public function canRequestModification(): bool
    {
        return $this->status === 'in_review';
    }

    // Accessors
    public function getSalaryRangeAttribute(): string
    {
        if ($this->salary_min && $this->salary_max) {
            return 'S/ ' . number_format($this->salary_min, 2) . ' - S/ ' . number_format($this->salary_max, 2);
        }
        return 'No especificado';
    }

    public function getEducationLevelLabelAttribute(): string
    {
        // Priorizar education_levels (múltiples) sobre education_level (único)
        if (!empty($this->education_levels)) {
            return \Modules\JobProfile\Enums\EducationLevelEnum::formatMultiple($this->education_levels);
        }

        if (!$this->education_level) {
            return 'No especificado';
        }

        return \Modules\JobProfile\Enums\EducationLevelEnum::from($this->education_level)->label();
    }

    public function getWorkRegimeLabelAttribute(): string
    {
        if (!$this->work_regime) {
            return 'No especificado';
        }
        return \Modules\JobProfile\Enums\WorkRegimeEnum::from($this->work_regime)->label();
    }

    public function getStatusLabelAttribute(): string
    {
        return \Modules\JobProfile\Enums\JobProfileStatusEnum::from($this->status)->label();
    }

    public function getStatusBadgeAttribute(): string
    {
        return \Modules\JobProfile\Enums\JobProfileStatusEnum::from($this->status)->badge();
    }

    public function getTotalExperienceYearsAttribute(): float
    {
        // Usamos 'attributes' para obtener el número crudo (float) de la BD
        // y evitamos que Laravel lo convierta en objeto ExperienceDuration
        $general = $this->attributes['general_experience_years'] ?? 0;
        $specific = $this->attributes['specific_experience_years'] ?? 0;

        return (float) $general + (float) $specific;
    }
    // En tu modelo JobProfile (después de los métodos existentes)

    /**
     * Obtiene el texto completo de la justificación basado en el valor almacenado
     */
    public function getJustificationTextAttribute(): string
    {
        $justifications = [
            'a' => 'Trabajos para obra o servicio específico, comprende la prestación de servicios para la realización de obras o servicios específicos que la entidad requiera atender en un periodo determinado.',
            'b' => 'Labores ocasionales o eventuales de duración determinada, son aquellas actividades excepcionales distintas a las labores habituales o regulares de la entidad.',
            'c' => 'Labores por incremento extraordinario y temporal de actividades, son aquellas actividades nuevas o ya existentes en la entidad y que se ven incrementadas a consecuencia de una situación estacional o coyuntural.',
            'd' => 'Labores para cubrir emergencias, son las que se generan por un caso fortuito o fuerza mayor.',
            'e' => 'Labores en Programas y Proyectos Especiales, son aquellas labores que mantienen su vigencia hasta la extinción de la entidad.',
            'f' => 'Cuando una norma con rango de ley autorice la contratación temporal para un fin específico.'
        ];

        return $justifications[$this->justification] ?? '';
    }

    /**
     * Obtiene las opciones de justificación para usar en formularios
     */
    public static function getJustificationOptions(): array
    {
        return [
            'a' => 'Trabajos para obra o servicio específico',
            'b' => 'Labores ocasionales o eventuales de duración determinada',
            'c' => 'Labores por incremento extraordinario y temporal de actividades',
            'd' => 'Labores para cubrir emergencias',
            'e' => 'Labores en Programas y Proyectos Especiales',
            'f' => 'Cuando una norma con rango de ley autorice la contratación temporal'
        ];
    }

    /**
     * Obtiene la duración del contrato formateada
     */
    public function getContractDurationAttribute(): string
    {
        if (!$this->contract_start_date || !$this->contract_end_date) {
            return '3 MESES';
        }

        return $this->contract_start_date->format('d \d\e F \d\e\l Y') .
               ' al ' .
               $this->contract_end_date->format('d \d\e F \d\e\l Y');
    }

    /**
     * Obtiene la remuneración mensual formateada para documentos
     */
    public function getFormattedSalaryAttribute(): string
    {
        if (!$this->positionCode) {
            return 'No especificado';
        }

        $salary = $this->positionCode->base_salary;
        return 'S/ ' . number_format($salary, 2) .
               ' (Incluye los montos y afiliaciones de Ley, así como toda deducción aplicable al trabajador.)';
    }

    /**
     * Obtiene los datos formateados para el Anexo 2
     */
    public function getAnexo2DataAttribute(): array
    {
        return [
            'identificacion_del_puesto' => [
                'unidad_organizacional_solicitante' => $this->requestingUnit?->name ?? 'No especificado',
                'denominacion' => $this->profile_name ?? 'No especificado',
                'cargo_requerido' => $this->positionCode?->name ?? 'No especificado',
                'codigo_de_cargo' => $this->positionCode?->code ?? 'No especificado',
                'vigencia_de_contrato' => $this->contract_duration,
            ],
            'funciones_especificas_a_desarrollar' => $this->main_functions ?? [],
            'formacion_academica' => [
                'descripcion' => $this->title_required ?? 'No especificado',
                'colegiado_y_habilitado' => [
                    'si' => $this->colegiatura_required,
                    'no' => !$this->colegiatura_required,
                ],
            ],
            'requisitos_generales' => $this->getRequisitosGenerales(),
            'requisitos_especificos' => [
                'experiencia_especifica' => $this->specific_experience_description ?? 'No especificado',
                'capacitacion' => $this->required_courses ?? [],
                'conocimientos' => $this->knowledge_areas ?? [],
            ],
        ];
    }

    /**
     * Obtiene los requisitos generales desde el PositionCode
     */
    public function getRequisitosGenerales(): string
    {
        if (!$this->positionCode) {
            return 'No especificado';
        }

        $parts = [];

        // Usar education_levels_accepted si está disponible
        if (!empty($this->positionCode->education_levels_accepted)) {
            $educationText = $this->positionCode->formatted_education_levels;
            $parts[] = $educationText;
        } elseif ($this->positionCode->education_level_required) {
            $parts[] = 'Nivel educativo: ' . ucfirst($this->positionCode->education_level_required);
        }

        if ($this->positionCode->requires_professional_title) {
            $parts[] = 'Título profesional requerido';
        }

        if ($this->positionCode->requires_professional_license) {
            $parts[] = 'Habilitación profesional vigente';
        }

        if ($this->positionCode->min_professional_experience > 0) {
            $parts[] = 'Experiencia profesional no menor a ' .
                      str_pad($this->positionCode->min_professional_experience, 2, '0', STR_PAD_LEFT) .
                      ' años';
        }

        if ($this->positionCode->min_specific_experience > 0) {
            $parts[] = 'Experiencia específica no menor a ' .
                      str_pad($this->positionCode->min_specific_experience, 2, '0', STR_PAD_LEFT) .
                      ' años en gestión pública';
        }

        return implode('. ', $parts) . '.';
    }

    /**
     * Obtiene los datos formateados para el Perfil Publicado
     */
    public function getPublishedProfileDataAttribute(): array
    {
        return [
            'oficina' => $this->organizationalUnit?->name ?? 'No especificado',
            'proceso_seleccion' => $this->selection_process_name ?? 'PROCESO DE SELECCIÓN CAS',
            'perfil_de_puesto' => [
                'area_solicitante' => $this->requestingUnit?->parent?->name ?? $this->organizationalUnit?->name ?? 'No especificado',
                'unidad_organica' => $this->requestingUnit?->name ?? 'No especificado',
                'denominacion' => strtoupper($this->profile_name ?? ''),
                'cargo_requerido' => strtoupper($this->positionCode?->name ?? ''),
                'codigo_de_cargo' => $this->positionCode?->code ?? '',
                'cantidad' => $this->total_vacancies,
                'justificacion_contratacion' => strtoupper($this->justification_text ?? ''),
            ],
            'requisitos_minimos' => [
                'formacion_academica' => strtoupper($this->title_required ?? ''),
                'experiencia_laboral_general' => 'EXPERIENCIA PROFESIONAL NO MENOR A ' .
                    str_pad($this->general_experience_years ?? 0, 2, '0', STR_PAD_LEFT) . ' AÑOS',
                'experiencia_laboral_especifica' => strtoupper($this->specific_experience_description ?? ''),
                'capacitaciones' => [
                    'descripcion' => 'ES NECESARIO ACREDITAR LAS CAPACITACIONES CON DOCUMENTOS COMO CONSTANCIAS, CERTIFICADOS O DIPLOMAS',
                    'lista' => array_map('strtoupper', $this->required_courses ?? []),
                ],
                'conocimientos' => array_map('strtoupper', $this->knowledge_areas ?? []),
                'competencias' => array_map('strtoupper', $this->required_competencies ?? []),
                'funciones' => array_map('strtoupper', $this->main_functions ?? []),
            ],
            'condiciones' => [
                'regimen_laboral' => strtoupper($this->work_regime_label ?? ''),
                'lugar_prestacion_servicios' => strtoupper($this->work_location ?? 'MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO'),
                'duracion_contrato' => strtoupper($this->contract_duration),
                'remuneracion_mensual' => $this->formatted_salary,
            ],
        ];
    }
}
