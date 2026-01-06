<?php

namespace Modules\Application\Entities;

use Modules\Core\Entities\BaseSoftDelete;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Entity AcademicCareer
 *
 * Catálogo maestro de carreras profesionales curadas.
 * Contiene 45 carreras base según análisis de uso real.
 */
class AcademicCareer extends BaseSoftDelete
{
    use HasUuids;

    protected $fillable = [
        'code',
        'name',
        'short_name',
        'sunedu_category',
        'category_group',
        'requires_colegiatura',
        'description',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'requires_colegiatura' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $searchable = ['code', 'name', 'sunedu_category', 'category_group'];
    protected $sortable = ['code', 'name', 'display_order', 'created_at'];

    /**
     * Relación con sinónimos
     */
    public function synonyms(): HasMany
    {
        return $this->hasMany(AcademicCareerSynonym::class, 'career_id');
    }

    /**
     * Relación con equivalencias (como carrera A)
     */
    public function equivalences(): HasMany
    {
        return $this->hasMany(AcademicCareerEquivalence::class, 'career_id');
    }

    /**
     * Relación con equivalencias (como carrera B)
     */
    public function equivalentTo(): HasMany
    {
        return $this->hasMany(AcademicCareerEquivalence::class, 'equivalent_career_id');
    }

    /**
     * Relación con job profiles (tabla pivote)
     */
    public function jobProfileCareers(): HasMany
    {
        return $this->hasMany(\Modules\JobProfile\Entities\JobProfileCareer::class, 'career_id');
    }

    /**
     * Relación con application academics
     */
    public function applicationAcademics(): HasMany
    {
        return $this->hasMany(ApplicationAcademic::class, 'career_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category_group', $category);
    }

    public function scopeRequiresColegiatura($query, bool $required = true)
    {
        return $query->where('requires_colegiatura', $required);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Obtener todas las carreras equivalentes (incluyendo la misma)
     */
    public function getAllEquivalentIds(): array
    {
        $ids = [$this->id];

        // Equivalencias donde esta carrera es A
        $equivalences = $this->equivalences()->pluck('equivalent_career_id')->toArray();
        $ids = array_merge($ids, $equivalences);

        // Equivalencias donde esta carrera es B
        $equivalentFrom = $this->equivalentTo()->pluck('career_id')->toArray();
        $ids = array_merge($ids, $equivalentFrom);

        return array_unique($ids);
    }

    /**
     * Verificar si tiene colegiatura requerida
     */
    public function requiresColegiatura(): bool
    {
        return $this->requires_colegiatura;
    }
}
