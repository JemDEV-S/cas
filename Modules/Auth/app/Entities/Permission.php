<?php

namespace Modules\Auth\Entities;

use Modules\Core\Entities\BaseSoftDelete;
use Modules\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Permission Entity
 *
 * Representa un permiso granular en el sistema.
 */
class Permission extends BaseSoftDelete
{
    use HasUuid;

    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $searchable = ['name', 'slug', 'description', 'module'];
    protected $sortable = ['name', 'module', 'created_at'];

    /**
     * Relación con roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_permission',
            'permission_id',
            'role_id'
        )->withTimestamps();
    }

    /**
     * Scope para filtrar por módulo.
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }
}
