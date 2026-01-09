<?php

namespace Modules\Results\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Results\Enums\PublicationPhaseEnum;
use Modules\Results\Enums\PublicationStatusEnum;
use Modules\Document\Entities\GeneratedDocument;
use Modules\JobPosting\Entities\JobPosting;
use App\Models\User;

class ResultPublication extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'job_posting_id',
        'phase',
        'status',
        'title',
        'description',

        // Documento oficial (con firmas)
        'generated_document_id',

        // Exportación Excel
        'excel_path',

        // Estadísticas
        'total_applicants',
        'total_eligible',
        'total_not_eligible',

        // Control de publicación
        'published_at',
        'published_by',
        'unpublished_at',
        'unpublished_by',

        // Metadata JSON
        'metadata',
    ];

    protected $casts = [
        'phase' => PublicationPhaseEnum::class,
        'status' => PublicationStatusEnum::class,
        'published_at' => 'datetime',
        'unpublished_at' => 'datetime',
        'metadata' => 'array',
        'total_applicants' => 'integer',
        'total_eligible' => 'integer',
        'total_not_eligible' => 'integer',
    ];

    /**
     * Relación con el documento oficial (PDF con firmas)
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(GeneratedDocument::class, 'generated_document_id');
    }

    /**
     * Relación con la convocatoria
     */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    /**
     * Usuario que publicó
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Usuario que despublicó
     */
    public function unpublisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unpublished_by');
    }

    /**
     * Verificar si está publicado y firmado
     */
    public function isPublished(): bool
    {
        return $this->status === PublicationStatusEnum::PUBLISHED &&
               $this->document?->isSigned();
    }

    /**
     * Verificar si está esperando firmas
     */
    public function isPendingSignature(): bool
    {
        return $this->status === PublicationStatusEnum::PENDING_SIGNATURE &&
               $this->document?->isPendingSignature();
    }

    /**
     * Obtener progreso de firmas
     */
    public function getSignatureProgress(): array
    {
        if (!$this->document) {
            return [
                'completed' => 0,
                'total' => 0,
                'percentage' => 0,
                'signers' => [],
            ];
        }

        $signatures = $this->document->signatures()
            ->with('user')
            ->orderBy('signature_order')
            ->get();

        return [
            'completed' => $this->document->signatures_completed ?? 0,
            'total' => $this->document->total_signatures_required ?? 0,
            'percentage' => $this->document->signature_progress ?? 0,
            'signers' => $signatures->map(function ($sig) {
                return [
                    'user' => $sig->user?->name,
                    'role' => $sig->role,
                    'status' => $sig->status,
                    'signed_at' => $sig->signed_at,
                ];
            })->toArray(),
        ];
    }

    /**
     * Verificar si puede ser despublicado
     */
    public function canBeUnpublished(): bool
    {
        // Solo puede despublicarse si no tiene firmas completadas
        return !$this->document?->hasAnySignature();
    }

    /**
     * Verificar si puede ser republicado
     */
    public function canBeRepublished(): bool
    {
        return $this->status === PublicationStatusEnum::UNPUBLISHED &&
               $this->document?->isSigned();
    }

    /**
     * Obtener URL del PDF firmado
     */
    public function getSignedPdfUrl(): ?string
    {
        if (!$this->document || !$this->document->signed_pdf_path) {
            return null;
        }

        return asset('storage/' . $this->document->signed_pdf_path);
    }

    /**
     * Obtener URL del Excel
     */
    public function getExcelUrl(): ?string
    {
        if (!$this->excel_path) {
            return null;
        }

        return asset('storage/' . $this->excel_path);
    }

    /**
     * Scope para publicaciones activas
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            PublicationStatusEnum::PENDING_SIGNATURE,
            PublicationStatusEnum::PUBLISHED
        ]);
    }

    /**
     * Scope para publicaciones visibles
     */
    public function scopeVisible($query)
    {
        return $query->where('status', PublicationStatusEnum::PUBLISHED);
    }

    /**
     * Scope por fase
     */
    public function scopeForPhase($query, PublicationPhaseEnum $phase)
    {
        return $query->where('phase', $phase);
    }
}
