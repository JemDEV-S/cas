<?php

namespace Modules\Application\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IaJob extends Model
{
    use HasUuids;

    protected $table = 'ia_jobs';

    protected $fillable = [
        'application_id',
        'job_profile_id',
        'applicant_career',
        'required_careers',
        'applicant_degree_type',
        'status',
        'resultado',
        'score',
        'justificacion',
        'attempts',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'attempts' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // --- Scopes ---

    public function scopePendiente($query)
    {
        return $query->where('status', 'pendiente');
    }

    public function scopeProcesando($query)
    {
        return $query->where('status', 'procesando');
    }

    // --- Relaciones ---

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(\Modules\JobProfile\Entities\JobProfile::class);
    }

    // --- Métodos ---

    public function markAsProcesando(): void
    {
        $this->update([
            'status' => 'procesando',
            'started_at' => now(),
            'attempts' => $this->attempts + 1,
        ]);
    }

    public function markAsCompletado(string $resultado, float $score, string $justificacion): void
    {
        $this->update([
            'status' => 'completado',
            'resultado' => $resultado,
            'score' => $score,
            'justificacion' => $justificacion,
            'completed_at' => now(),
        ]);
    }

    public function markAsError(string $errorMessage): void
    {
        $this->update([
            'status' => $this->attempts >= 3 ? 'error' : 'pendiente',
            'error_message' => $errorMessage,
        ]);
    }
}
