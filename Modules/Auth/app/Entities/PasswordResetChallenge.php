<?php

namespace Modules\Auth\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Core\Traits\HasUuid;
use Modules\User\Entities\User;

class PasswordResetChallenge extends Model
{
    use HasUuid;

    protected $table = 'password_reset_challenges';

    protected $fillable = [
        'user_id',
        'dni',
        'questions',
        'current_index',
        'attempts_used',
        'max_attempts',
        'status',
        'verified_at',
        'reset_token',
        'reset_token_expires_at',
        'ip_address',
        'user_agent',
        'expires_at',
    ];

    protected $casts = [
        'questions' => 'array',
        'verified_at' => 'datetime',
        'reset_token_expires_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? true;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function currentQuestion(): ?array
    {
        $questions = $this->questions ?? [];
        return $questions[$this->current_index] ?? null;
    }

    public function totalQuestions(): int
    {
        return count($this->questions ?? []);
    }

    public function attemptsRemaining(): int
    {
        return max(0, $this->max_attempts - $this->attempts_used);
    }

    public function markFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    public function markVerified(): string
    {
        $token = Str::random(64);
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'reset_token' => $token,
            'reset_token_expires_at' => now()->addMinutes(15),
        ]);
        return $token;
    }

    public function isResetTokenValid(): bool
    {
        return $this->status === 'verified'
            && $this->reset_token
            && $this->reset_token_expires_at
            && $this->reset_token_expires_at->isFuture();
    }
}
