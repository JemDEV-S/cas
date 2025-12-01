<?php

namespace Modules\Document\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAudit extends BaseModel
{
    use HasUuid;

    protected $fillable = [
        'generated_document_id',
        'user_id',
        'action',
        'description',
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

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Métodos estáticos para registro
    public static function log(
        string $documentId,
        string $action,
        string $userId,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        return self::create([
            'generated_document_id' => $documentId,
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // Accessors
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'Creado',
            'updated' => 'Actualizado',
            'viewed' => 'Visualizado',
            'downloaded' => 'Descargado',
            'signed' => 'Firmado',
            'rejected' => 'Rechazado',
            'deleted' => 'Eliminado',
            'restored' => 'Restaurado',
            default => $this->action,
        };
    }
}
