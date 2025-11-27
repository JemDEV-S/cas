<?php

namespace Modules\Document\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalSignature extends BaseModel
{
    use HasUuid;

    protected $fillable = [
        'generated_document_id',
        'user_id',
        'signature_type',
        'signature_order',
        'role',
        'certificate_data',
        'certificate_issuer',
        'certificate_serial',
        'certificate_valid_from',
        'certificate_valid_to',
        'signed_document_path',
        'signature_timestamp',
        'signed_at',
        'ip_address',
        'user_agent',
        'status',
        'rejection_reason',
        'signature_metadata',
    ];

    protected $casts = [
        'certificate_data' => 'array',
        'certificate_valid_from' => 'datetime',
        'certificate_valid_to' => 'datetime',
        'signature_timestamp' => 'datetime',
        'signed_at' => 'datetime',
        'signature_metadata' => 'array',
        'signature_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function document(): BelongsTo
    {
        return $this->belongsTo(GeneratedDocument::class, 'generated_document_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class);
    }

    // Scopes
    public function scopeByDocument($query, string $documentId)
    {
        return $query->where('generated_document_id', $documentId);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByOrder($query)
    {
        return $query->orderBy('signature_order');
    }

    // Métodos
    public function isSigned(): bool
    {
        return $this->status === 'signed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCertificateValid(): bool
    {
        if (!$this->certificate_valid_from || !$this->certificate_valid_to) {
            return false;
        }

        $now = now();
        return $now->between($this->certificate_valid_from, $this->certificate_valid_to);
    }

    // Accessors
    public function getSignatureTypeLabelAttribute(): string
    {
        return match($this->signature_type) {
            'firma' => 'Firma',
            'visto_bueno' => 'Visto Bueno',
            'aprobacion' => 'Aprobación',
            'conformidad' => 'Conformidad',
            default => 'Firma',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'signed' => 'Firmado',
            'rejected' => 'Rechazado',
            'cancelled' => 'Cancelado',
            default => 'Desconocido',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'signed' => 'badge-success',
            'rejected' => 'badge-danger',
            'cancelled' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }
}
