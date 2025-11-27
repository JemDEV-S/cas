<?php

namespace Modules\Organization\Entities;

use Modules\Core\Entities\BaseSoftDelete;
use Modules\Core\Traits\HasUuid;
use Modules\Core\Traits\HasStatus;
use Modules\Core\Traits\HasMetadata;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrganizationalUnit extends BaseSoftDelete
{
    use HasUuid, HasStatus, HasMetadata;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'parent_id',
        'level',
        'path',
        'order',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'level' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $searchable = ['code', 'name', 'description'];
    protected $sortable = ['name', 'code', 'type', 'level', 'order'];

    /**
     * Relación muchos a muchos con User a través de UserOrganizationUnit
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            \Modules\User\Entities\User::class,
            'user_organization_units',
            'organization_unit_id',
            'user_id'
        )
        ->withPivot(['start_date', 'end_date', 'is_primary', 'is_active', 'id'])
        ->withTimestamps()
        ->using(\Modules\User\Entities\UserOrganizationUnit::class);
    }

    /**
     * Relación directa con los registros de asignación
     */
    public function userOrganizationUnits(): HasMany
    {
        return $this->hasMany(\Modules\User\Entities\UserOrganizationUnit::class, 'organization_unit_id');
    }

    /**
     * Obtener usuarios activos de esta unidad organizacional
     */
    public function activeUsers()
    {
        return $this->users()
            ->wherePivot('is_active', true)
            ->get();
    }

    /**
     * Obtener usuarios con asignación primaria a esta unidad
     */
    public function primaryUsers()
    {
        return $this->users()
            ->wherePivot('is_primary', true)
            ->wherePivot('is_active', true)
            ->get();
    }
    /**
     * Relación con unidad padre
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'parent_id');
    }

    /**
     * Relación con unidades hijas
     */
    public function children(): HasMany
    {
        return $this->hasMany(OrganizationalUnit::class, 'parent_id')
            ->orderBy('order');
    }

    /**
     * Relación con ancestros (Closure Table)
     */
    public function ancestors(): BelongsToMany
    {
        return $this->belongsToMany(
            OrganizationalUnit::class,
            'organizational_unit_closure',
            'descendant_id',
            'ancestor_id'
        )->withPivot('depth');
    }

    /**
     * Relación con descendientes (Closure Table)
     */
    public function descendants(): BelongsToMany
    {
        return $this->belongsToMany(
            OrganizationalUnit::class,
            'organizational_unit_closure',
            'ancestor_id',
            'descendant_id'
        )->withPivot('depth');
    }

    /**
     * Scope para unidades raíz (sin padre)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope por tipo de unidad
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope por nivel
     */
    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Verifica si es unidad raíz
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Verifica si tiene hijos
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Obtiene la ruta completa de nombres
     */
    public function getFullPathAttribute(): string
    {
        if ($this->isRoot()) {
            return $this->name;
        }

        $ancestors = $this->ancestors()
            ->orderBy('organizational_unit_closure.depth', 'desc')
            ->get(['name']);

        $names = $ancestors->pluck('name')->push($this->name);

        return $names->join(' > ');
    }

    /**
     * Obtiene todos los descendientes directos e indirectos
     */
    public function getAllDescendants()
    {
        return $this->descendants()->with('descendants')->get();
    }

    /**
     * Obtiene todos los ancestros ordenados
     */
    public function getAllAncestors()
    {
        return $this->ancestors()
            ->orderBy('organizational_unit_closure.depth', 'desc')
            ->get();
    }
}
