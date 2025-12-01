<?php

namespace Modules\Document\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignatureWorkflow extends BaseModel
{
    use HasUuid;

    protected $fillable = [
        'generated_document_id',
        'workflow_type',
        'current_step',
        'total_steps',
        'signers_order',
        'status',
        'started_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'signers_order' => 'array',
        'current_step' => 'integer',
        'total_steps' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function document(): BelongsTo
    {
        return $this->belongsTo(GeneratedDocument::class, 'generated_document_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeSequential($query)
    {
        return $query->where('workflow_type', 'sequential');
    }

    public function scopeParallel($query)
    {
        return $query->where('workflow_type', 'parallel');
    }

    // Métodos
    public function isActive(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isSequential(): bool
    {
        return $this->workflow_type === 'sequential';
    }

    public function isParallel(): bool
    {
        return $this->workflow_type === 'parallel';
    }

    public function getCurrentSigner(): ?array
    {
        if ($this->isCompleted() || $this->isCancelled()) {
            return null;
        }

        $signersOrder = $this->signers_order ?? [];
        return $signersOrder[$this->current_step - 1] ?? null;
    }

    public function getNextSigner(): ?array
    {
        $signersOrder = $this->signers_order ?? [];
        return $signersOrder[$this->current_step] ?? null;
    }

    public function advanceStep(): void
    {
        if ($this->current_step < $this->total_steps) {
            $this->current_step++;
            $this->save();
        } else {
            $this->markAsCompleted();
        }
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function cancel(string $reason): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    // Accessors
    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_steps === 0) {
            return 0;
        }
        return ($this->current_step / $this->total_steps) * 100;
    }

    public function getWorkflowTypeLabelAttribute(): string
    {
        return match($this->workflow_type) {
            'sequential' => 'Secuencial',
            'parallel' => 'Paralelo',
            default => 'Desconocido',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'in_progress' => 'En Proceso',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
            default => 'Desconocido',
        };
    }
}
