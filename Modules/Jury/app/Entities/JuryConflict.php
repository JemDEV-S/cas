<?php

namespace Modules\Jury\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Jury\Enums\{ConflictType, ConflictSeverity, ConflictStatus};
use Modules\JobPosting\Entities\JobPosting;
use Illuminate\Support\Str;

class JuryConflict extends Model
{
    use HasFactory;

    protected $table = 'jury_conflicts';

    protected $fillable = [
        'user_id',
        'application_id',
        'type',
        'description',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Relaciones
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('Modules\User\Entities\User', 'user_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo('Modules\Application\Entities\Application');
    }

    /**
     * Scopes
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByApplication($query, string $applicationId)
    {
        return $query->where('application_id', $applicationId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeFamily($query)
    {
        return $query->where('type', 'FAMILY');
    }

    public function scopePersonal($query)
    {
        return $query->where('type', 'PERSONAL');
    }

    /**
     * Helper Methods
     */
    public static function hasConflict(string $userId, string $applicationId): bool
    {
        return static::where('user_id', $userId)
            ->where('application_id', $applicationId)
            ->exists();
    }

    /**
     * Attributes
     */
    public function getUserNameAttribute(): string
    {
        return $this->user->name ?? 'N/A';
    }

    public function getApplicantNameAttribute(): string
    {
        return $this->application->full_name ?? 'N/A';
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'FAMILY' => 'Parentesco',
            'PERSONAL' => 'Relación Personal',
            default => $this->type
        };
    }
}