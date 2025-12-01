<?php

namespace Modules\Document\Entities;

use Modules\Core\Entities\BaseSoftDelete;
use Modules\Core\Traits\HasUuid;
use Modules\Core\Traits\HasStatus;
use Modules\Core\Traits\HasMetadata;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeneratedDocument extends BaseSoftDelete
{
    use HasUuid, HasStatus, HasMetadata;

    protected $fillable = [
        'code',
        'document_template_id',
        'documentable_id',
        'documentable_type',
        'title',
        'content',
        'rendered_html',
        'pdf_path',
        'signed_pdf_path',
        'status',
        'generated_by',
        'generated_at',
        'signature_required',
        'signature_status',
        'signatures_completed',
        'total_signatures_required',
        'current_signer_id',
        'metadata',
    ];

    protected $casts = [
        'signature_required' => 'boolean',
        'signatures_completed' => 'integer',
        'total_signatures_required' => 'integer',
        'generated_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $searchable = ['code', 'title'];
    protected $sortable = ['code', 'title', 'status', 'created_at', 'generated_at'];

    // Relaciones
    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class, 'generated_by');
    }

    public function currentSigner(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class, 'current_signer_id');
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(DigitalSignature::class)->orderBy('signature_order');
    }

    public function signatureWorkflow(): HasMany
    {
        return $this->hasMany(SignatureWorkflow::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(DocumentAudit::class)->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePendingSignature($query)
    {
        return $query->where('signature_status', 'pending');
    }

    public function scopeSigned($query)
    {
        return $query->where('signature_status', 'completed');
    }

    public function scopeForDocumentable($query, string $type, string $id)
    {
        return $query->where('documentable_type', $type)
                     ->where('documentable_id', $id);
    }

    // Métodos de estado
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPendingSignature(): bool
    {
        return $this->signature_status === 'pending';
    }

    public function isSigned(): bool
    {
        return $this->signature_status === 'completed';
    }

    public function requiresSignature(): bool
    {
        return $this->signature_required === true;
    }

    public function isFullySigned(): bool
    {
        return $this->signatures_completed >= $this->total_signatures_required;
    }

    public function canBeSignedBy(string $userId): bool
    {
        return $this->current_signer_id === $userId &&
               $this->signature_status === 'in_progress';
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Borrador',
            'pending_signature' => 'Pendiente de Firma',
            'signed' => 'Firmado',
            'rejected' => 'Rechazado',
            'cancelled' => 'Cancelado',
            default => 'Desconocido',
        };
    }

    public function getSignatureStatusLabelAttribute(): string
    {
        return match($this->signature_status) {
            'pending' => 'Pendiente',
            'in_progress' => 'En Proceso',
            'completed' => 'Completado',
            'rejected' => 'Rechazado',
            default => 'Sin Firma',
        };
    }

    public function getSignatureProgressAttribute(): float
    {
        if ($this->total_signatures_required === 0) {
            return 0;
        }
        return ($this->signatures_completed / $this->total_signatures_required) * 100;
    }
}
