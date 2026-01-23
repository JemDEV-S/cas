<?php

namespace Modules\Application\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'code',
        'job_profile_id',        // ← NUEVO: relación directa con el perfil
        'assigned_vacancy_id',   // ← RENOMBRADO (era job_profile_vacancy_id)
        'applicant_id',
        'status',
        'application_date',
        'terms_accepted',
        'full_name',
        'dni',
        'birth_date',
        'address',
        'phone',
        'mobile_phone',
        'email',
        'is_eligible',
        'eligibility_checked_by',
        'eligibility_checked_at',
        'ineligibility_reason',
        'requires_amendment',
        'amendment_deadline',
        'amendment_notes',
        'curriculum_score',
        'interview_score',
        'special_condition_bonus',
        'final_score',
        'final_ranking',
        'ip_address',
        'notes',
    ];

    protected $casts = [
        'status' => \Modules\Application\Enums\ApplicationStatus::class,
        'application_date' => 'datetime',
        'birth_date' => 'date',
        'terms_accepted' => 'boolean',
        'is_eligible' => 'boolean',
        'eligibility_checked_at' => 'datetime',
        'requires_amendment' => 'boolean',
        'amendment_deadline' => 'date',
        'curriculum_score' => 'decimal:2',
        'interview_score' => 'decimal:2',
        'special_condition_bonus' => 'decimal:2',
        'final_score' => 'decimal:2',
        'final_ranking' => 'integer',
    ];

    /**
     * Perfil al que postula (relación principal)
     */
    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(\Modules\JobProfile\Entities\JobProfile::class, 'job_profile_id');
    }

    /**
     * Vacante asignada si ganó (puede ser NULL)
     */
    public function assignedVacancy(): BelongsTo
    {
        return $this->belongsTo(\Modules\JobProfile\Entities\JobProfileVacancy::class, 'assigned_vacancy_id');
    }

    /**
     * @deprecated Use assignedVacancy() instead. Maintained for backwards compatibility.
     */
    public function vacancy(): BelongsTo
    {
        return $this->assignedVacancy();
    }

    /**
     * Relación con el postulante
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'applicant_id');
    }

    /**
     * Relación con quien verificó la elegibilidad
     */
    public function eligibilityChecker(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'eligibility_checked_by');
    }

    /**
     * Relación con formación académica
     */
    public function academics(): HasMany
    {
        return $this->hasMany(ApplicationAcademic::class);
    }

    /**
     * Relación con experiencia laboral
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(ApplicationExperience::class);
    }

    /**
     * Experiencia general
     */
    public function generalExperiences(): HasMany
    {
        return $this->hasMany(ApplicationExperience::class)->where('is_specific', false);
    }

    /**
     * Experiencia específica
     */
    public function specificExperiences(): HasMany
    {
        return $this->hasMany(ApplicationExperience::class)->where('is_specific', true);
    }

    /**
     * Relación con capacitaciones/cursos
     */
    public function trainings(): HasMany
    {
        return $this->hasMany(ApplicationTraining::class);
    }

    /**
     * Relación con condiciones especiales
     */
    public function specialConditions(): HasMany
    {
        return $this->hasMany(ApplicationSpecialCondition::class);
    }

    /**
     * Relación con registros profesionales
     */
    public function professionalRegistrations(): HasMany
    {
        return $this->hasMany(ApplicationProfessionalRegistration::class);
    }

    /**
     * Relación con conocimientos
     */
    public function knowledge(): HasMany
    {
        return $this->hasMany(ApplicationKnowledge::class);
    }

    /**
     * Relación con documentos
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    /**
     * Relación con historial
     */
    public function history(): HasMany
    {
        return $this->hasMany(ApplicationHistory::class)->orderBy('performed_at', 'desc');
    }

    /**
     * Relación polimórfica con documentos generados
     */
    public function generatedDocuments()
    {
        return $this->morphMany(\Modules\Document\Entities\GeneratedDocument::class, 'documentable');
    }

    /**
     * Relación con evaluaciones automáticas
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(\Modules\Evaluation\Entities\Evaluation::class)->orderBy('submitted_at', 'desc');
    }

    /**
     * Obtener la última evaluación
     */
    public function latestEvaluation()
    {
        return $this->hasOne(ApplicationEvaluation::class)->latestOfMany('evaluated_at');
    }

    /**
     * Relación con override de elegibilidad (reevaluación de reclamo)
     */
    public function eligibilityOverride(): HasOne
    {
        return $this->hasOne(EligibilityOverride::class);
    }

    /**
     * Verificar si la postulación está en estado editable
     */
    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    /**
     * Verificar si está evaluada
     */
    public function isEvaluated(): bool
    {
        return !is_null($this->curriculum_score);
    }

    /**
     * Obtener el nombre completo formateado
     */
    public function getFormattedNameAttribute(): string
    {
        return strtoupper($this->full_name);
    }

    /**
     * Generar código único de postulación
     *
     * Usa bloqueo pesimista para evitar race conditions cuando
     * múltiples usuarios postulan simultáneamente.
     */
    public static function generateCode(string $convocatoriaCode): string
    {
        $year = date('Y');
        $prefix = "APP-{$year}-";

        // Usar bloqueo pesimista para evitar duplicados por concurrencia
        // SUBSTRING_INDEX extrae el número después del último guion
        $lastCode = static::where('code', 'LIKE', "{$prefix}%")
            ->orderByRaw("CAST(SUBSTRING_INDEX(code, '-', -1) AS UNSIGNED) DESC")
            ->lockForUpdate()
            ->first();

        if ($lastCode) {
            // Extraer el número del código
            $lastNumber = (int) str_replace($prefix, '', $lastCode->code);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "{$prefix}{$newNumber}";
    }

    /**
     * Verificar si ganó una vacante
     */
    public function hasWon(): bool
    {
        return !is_null($this->assigned_vacancy_id);
    }

    /**
     * Verificar si está pendiente de asignación
     */
    public function isPendingAssignment(): bool
    {
        return $this->is_eligible && is_null($this->assigned_vacancy_id);
    }

    /**
     * Scope: Solo ganadores
     */
    public function scopeWinners($query)
    {
        return $query->whereNotNull('assigned_vacancy_id');
    }

    /**
     * Scope: Elegibles sin vacante asignada
     */
    public function scopePendingAssignment($query)
    {
        return $query->where('is_eligible', true)
                     ->whereNull('assigned_vacancy_id');
    }

    public function evaluatorAssignments()
    {
        return $this->hasMany(\Modules\Evaluation\Entities\EvaluatorAssignment::class);
    }
}
