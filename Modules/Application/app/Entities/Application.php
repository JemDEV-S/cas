<?php

namespace Modules\Application\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'code',
        'job_profile_vacancy_id',
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
     * Relación con la vacante del perfil de trabajo
     */
    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(\Modules\JobProfile\Entities\JobProfileVacancy::class, 'job_profile_vacancy_id');
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
     * Verificar si la postulación está en estado editable
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['PRESENTADA', 'EN_REVISION', 'SUBSANACION']);
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
     */
    public static function generateCode(string $convocatoriaCode): string
    {
        $year = date('Y');
        $lastCode = static::where('code', 'LIKE', "APP-{$year}-%")
            ->orderBy('code', 'desc')
            ->first();

        if ($lastCode) {
            $lastNumber = (int) substr($lastCode->code, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "APP-{$year}-{$newNumber}";
    }
}
