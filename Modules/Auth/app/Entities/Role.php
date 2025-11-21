<?php

namespace Modules\Auth\Entities;

use Modules\Core\Entities\BaseSoftDelete;
use Modules\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Role Entity
 *
 * Representa un rol en el sistema.
 */
class Role extends BaseSoftDelete
{
    use HasUuid;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $searchable = ['name', 'slug', 'description'];
    protected $sortable = ['name', 'created_at'];

    /**
     * Relación con permisos.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permission',
            'role_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * Verifica si el rol tiene un permiso específico.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    /**
     * Asigna un permiso al rol.
     */
    public function givePermission(Permission $permission): void
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions()->attach($permission);
        }
    }

    /**
     * Revoca un permiso del rol.
     */
    public function revokePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission);
    }

    /**
     * Sincroniza permisos del rol.
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }
}
