<?php

namespace Modules\Application\Entities;

use Modules\Core\Entities\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entity AcademicCareerSynonym
 *
 * Sinónimos y variantes de nombres de carreras.
 * Permite matching flexible con dataset SUNEDU y legacy data.
 */
class AcademicCareerSynonym extends BaseModel
{
    use HasUuids;

    protected $fillable = [
        'career_id',
        'synonym',
        'source',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $searchable = ['synonym'];
    protected $sortable = ['synonym', 'source', 'created_at'];

    /**
     * Relación con la carrera
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(AcademicCareer::class, 'career_id');
    }

    /**
     * Scopes
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeFromSunedu($query)
    {
        return $query->where('source', 'SUNEDU');
    }

    public function scopeFromLegacy($query)
    {
        return $query->where('source', 'LEGACY');
    }

    public function scopeManual($query)
    {
        return $query->where('source', 'MANUAL');
    }

    /**
     * Buscar carrera por sinónimo (case-insensitive)
     */
    public static function findCareerBySynonym(string $synonym): ?AcademicCareer
    {
        $normalized = self::normalizeName($synonym);

        $synonymEntity = self::approved()
            ->whereRaw('LOWER(synonym) = ?', [$normalized])
            ->first();

        return $synonymEntity?->career;
    }

    /**
     * Normalizar nombre para comparación
     */
    public static function normalizeName(string $name): string
    {
        // Remover tildes
        $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
        // Convertir a minúsculas
        $name = strtolower($name);
        // Remover caracteres especiales excepto espacios
        $name = preg_replace('/[^a-z0-9\s]/', '', $name);
        // Remover espacios múltiples
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name);
    }
}
