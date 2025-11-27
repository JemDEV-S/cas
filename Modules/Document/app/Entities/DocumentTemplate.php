<?php

namespace Modules\Document\Entities;

use Modules\Core\Entities\BaseSoftDelete;
use Modules\Core\Traits\HasUuid;
use Modules\Core\Traits\HasStatus;
use Modules\Core\Traits\HasMetadata;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTemplate extends BaseSoftDelete
{
    use HasUuid, HasStatus, HasMetadata;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'content',
        'variables',
        'signature_required',
        'signature_positions',
        'signature_workflow_type',
        'signers_config',
        'paper_size',
        'orientation',
        'margins',
        'header_content',
        'footer_content',
        'watermark',
        'status',
        'created_by',
        'updated_by',
        'metadata',
    ];

    protected $casts = [
        'variables' => 'array',
        'signature_positions' => 'array',
        'signers_config' => 'array',
        'margins' => 'array',
        'signature_required' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $searchable = ['code', 'name', 'description', 'category'];
    protected $sortable = ['code', 'name', 'category', 'status', 'created_at'];

    // Relaciones
    public function generatedDocuments(): HasMany
    {
        return $this->hasMany(GeneratedDocument::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class, 'updated_by');
    }

    // Scopes
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRequiresSignature($query)
    {
        return $query->where('signature_required', true);
    }

    // Métodos
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function requiresSignature(): bool
    {
        return $this->signature_required === true;
    }

    public function getVariablesList(): array
    {
        return $this->variables ?? [];
    }

    public function getSignatureWorkflowType(): string
    {
        return $this->signature_workflow_type ?? 'sequential';
    }

    // Accessors
    public function getCategoryLabelAttribute(): string
    {
        return \Modules\Document\Enums\DocumentCategoryEnum::from($this->category)->label();
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            'draft' => 'Borrador',
            default => 'Desconocido',
        };
    }
}
