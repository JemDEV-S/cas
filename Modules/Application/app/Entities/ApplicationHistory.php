<?php

namespace Modules\Application\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationHistory extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'application_history';

    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'event_type',
        'old_status',
        'new_status',
        'old_values',
        'new_values',
        'description',
        'comments',
        'performed_by',
        'ip_address',
        'user_agent',
        'metadata',
        'performed_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * Relación con la postulación
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relación con usuario que realizó la acción
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'performed_by');
    }

    /**
     * Obtener el nombre del tipo de evento
     */
    public function getEventTypeNameAttribute(): string
    {
        return match($this->event_type) {
            'CREATED' => 'Creación',
            'UPDATED' => 'Actualización',
            'STATUS_CHANGED' => 'Cambio de Estado',
            'DOCUMENT_UPLOADED' => 'Documento Subido',
            'DOCUMENT_DELETED' => 'Documento Eliminado',
            'EVALUATED' => 'Evaluación',
            'COMMENTED' => 'Comentario',
            'AMENDMENT_REQUESTED' => 'Subsanación Solicitada',
            'WITHDRAWN' => 'Desistimiento',
            default => $this->event_type,
        };
    }

    /**
     * Obtener descripción formateada del cambio
     */
    public function getFormattedChangeAttribute(): string
    {
        if ($this->event_type === 'STATUS_CHANGED') {
            return "Estado cambió de '{$this->old_status}' a '{$this->new_status}'";
        }

        if ($this->old_values && $this->new_values) {
            $changes = [];
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? 'N/A';
                $changes[] = "{$key}: {$oldValue} → {$newValue}";
            }
            return implode(', ', $changes);
        }

        return $this->description ?? 'Sin detalles';
    }

    /**
     * Crear registro de historial
     */
    public static function log(
        string $applicationId,
        string $eventType,
        array $data = [],
        string $description = null
    ): self {
        return self::create([
            'application_id' => $applicationId,
            'event_type' => $eventType,
            'old_status' => $data['old_status'] ?? null,
            'new_status' => $data['new_status'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'description' => $description,
            'comments' => $data['comments'] ?? null,
            'performed_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $data['metadata'] ?? null,
            'performed_at' => now(),
        ]);
    }
}
