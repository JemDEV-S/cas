<?php

namespace Modules\JobPosting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasUuid;
use Modules\User\Entities\User;

class JobPostingHistory extends Model
{
    use HasUuid;

    protected $table = 'job_posting_history';
    public $timestamps = false;

    protected $fillable = [
        'job_posting_id',
        'user_id',
        'action',
        'old_status',
        'new_status',
        'old_values',
        'new_values',
        'description',
        'reason',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Convocatoria
     */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    /**
     * Usuario que realizó la acción
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Crear registro de historial
     */
    public static function log(
        JobPosting $jobPosting,
        string $action,
        ?User $user = null,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?string $reason = null
    ): self {
        return self::create([
            'job_posting_id' => $jobPosting->id,
            'user_id' => $user?->id,
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}