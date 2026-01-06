<?php

namespace Modules\Application\Entities;

use Modules\Core\Entities\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entity AcademicCareerEquivalence
 *
 * Equivalencias aprobadas entre carreras profesionales.
 * Ejemplo: Ingeniería de Sistemas ≡ Ingeniería Informática
 */
class AcademicCareerEquivalence extends BaseModel
{
    use HasUuids;

    protected $fillable = [
        'career_id',
        'equivalent_career_id',
        'equivalence_type',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $searchable = ['notes'];
    protected $sortable = ['equivalence_type', 'approved_at', 'created_at'];

    /**
     * Relación con la carrera A
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(AcademicCareer::class, 'career_id');
    }

    /**
     * Relación con la carrera B (equivalente)
     */
    public function equivalentCareer(): BelongsTo
    {
        return $this->belongsTo(AcademicCareer::class, 'equivalent_career_id');
    }

    /**
     * Relación con el usuario que aprobó
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopeByCareer($query, string $careerId)
    {
        return $query->where(function($q) use ($careerId) {
            $q->where('career_id', $careerId)
              ->orWhere('equivalent_career_id', $careerId);
        });
    }

    public function scopeManual($query)
    {
        return $query->where('equivalence_type', 'MANUAL');
    }

    public function scopeByCategoryGroup($query)
    {
        return $query->where('equivalence_type', 'CATEGORY_GROUP');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_by')->whereNotNull('approved_at');
    }

    /**
     * Obtener todas las carreras equivalentes a una dada
     */
    public static function getEquivalentCareerIds(string $careerId): array
    {
        $ids = [$careerId];

        $equivalences = self::where(function($q) use ($careerId) {
            $q->where('career_id', $careerId)
              ->orWhere('equivalent_career_id', $careerId);
        })->get();

        foreach ($equivalences as $equiv) {
            $ids[] = $equiv->career_id;
            $ids[] = $equiv->equivalent_career_id;
        }

        return array_unique($ids);
    }
}
