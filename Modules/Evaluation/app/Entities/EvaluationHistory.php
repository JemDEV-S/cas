<?php

namespace Modules\Evaluation\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class EvaluationHistory extends Model
{
    use HasFactory;

    protected $table = 'evaluation_history';

    protected $fillable = [
        'uuid',
        'evaluation_id',
        'user_id',
        'action_type',
        'description',
        'reason',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relaciones
     */
    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Scopes
     */
    public function scopeByEvaluation($query, $evaluationId)
    {
        return $query->where('evaluation_id', $evaluationId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByActionType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Helper para registrar cambios
     */
    public static function logChange(
        int $evaluationId,
        int $userId,
        string $actionType,
        string $description = null,
        array $oldValues = null,
        array $newValues = null,
        string $reason = null
    ): self {
        return static::create([
            'evaluation_id' => $evaluationId,
            'user_id' => $userId,
            'action_type' => $actionType,
            'description' => $description,
            'reason' => $reason,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}