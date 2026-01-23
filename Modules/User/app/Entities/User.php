<?php

namespace Modules\User\Entities;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Traits\HasUuid;
use Modules\Core\Traits\HasStatus;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, HasUuid, HasStatus, SoftDeletes;

    protected $fillable = [
        'dni',
        'email',
        'password',
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'address',
        'district',
        'province',
        'department',
        'phone',
        'photo_url',
        'email_verified_at',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $searchable = ['dni', 'email', 'first_name', 'last_name'];
    protected $sortable = ['first_name', 'last_name', 'email', 'created_at'];

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function preference(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    /**
     * Relación muchos a muchos con OrganizationalUnit a través de UserOrganizationUnit
     */
    public function organizationUnits(): BelongsToMany
    {
        return $this->belongsToMany(
            \Modules\Organization\Entities\OrganizationalUnit::class,
            'user_organization_units',
            'user_id',
            'organization_unit_id'
        )
        ->withPivot(['start_date', 'end_date', 'is_primary', 'is_active', 'id'])
        ->withTimestamps()
        ->using(UserOrganizationUnit::class);
    }

    /**
     * Relación directa con los registros de asignación
     */
    public function userOrganizationUnits(): HasMany
    {
        return $this->hasMany(UserOrganizationUnit::class);
    }

    /**
     * Obtener la unidad organizacional primaria del usuario
     */
    public function primaryOrganizationUnit()
    {
        return $this->organizationUnits()
            ->wherePivot('is_primary', true)
            ->wherePivot('is_active', true)
            ->first();
    }

    /**
     * Obtener las unidades organizacionales activas del usuario
     */
    public function activeOrganizationUnits()
    {
        return $this->organizationUnits()
            ->wherePivot('is_active', true)
            ->get();
    }

    /**
     * Obtener todos los IDs de unidades organizacionales del usuario
     * incluyendo todas las unidades descendientes (hijas, nietas, etc.)
     *
     * @return array
     */
    public function getAllOrganizationUnitIds(): array
    {
        $userUnits = $this->activeOrganizationUnits();

        if ($userUnits->isEmpty()) {
            return [];
        }

        $allUnitIds = [];

        foreach ($userUnits as $unit) {
            // Agregar la unidad del usuario
            $allUnitIds[] = $unit->id;

            // Obtener todas las descendientes usando la closure table
            $descendants = \DB::table('organizational_unit_closure')
                ->where('ancestor_id', $unit->id)
                ->where('descendant_id', '!=', $unit->id) // Excluir self-reference
                ->pluck('descendant_id')
                ->toArray();

            $allUnitIds = array_merge($allUnitIds, $descendants);
        }

        return array_unique($allUnitIds);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            \Modules\Auth\Entities\Role::class,
            'user_role',
            'user_id',
            'role_id'
        )->withTimestamps();
    }

    /**
     * Perfiles de puesto solicitados por el usuario
     */
    public function requestedJobProfiles(): HasMany
    {
        if (!class_exists('\Modules\JobProfile\Entities\JobProfile')) {
            return null;
        }

        return $this->hasMany(\Modules\JobProfile\Entities\JobProfile::class, 'requested_by');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    public function assignRole($role): void
    {
        if (is_string($role)) {
            $role = \Modules\Auth\Entities\Role::where('slug', $role)->firstOrFail();
        }

        if (!$this->roles->contains($role)) {
            $this->roles()->attach($role);
        }
    }

    public function removeRole($role): void
    {
        if (is_string($role)) {
            $role = \Modules\Auth\Entities\Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->detach($role);
    }

    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
    }

    /**
     * Verificar si el usuario es super-admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Super-admin tiene todos los permisos
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Cargar roles con sus permisos si no están cargados
        if (!$this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        }

        // Verificar en todos los roles del usuario
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar si el usuario tiene alguno de los permisos especificados
     */
    public function hasAnyPermission(array $permissionSlugs): bool
    {
        // Super-admin tiene todos los permisos
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissionSlugs as $permissionSlug) {
            if ($this->hasPermission($permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar si el usuario tiene todos los permisos especificados
     */
    public function hasAllPermissions(array $permissionSlugs): bool
    {
        // Super-admin tiene todos los permisos
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissionSlugs as $permissionSlug) {
            if (!$this->hasPermission($permissionSlug)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtener todos los permisos del usuario (a través de sus roles)
     */
    public function getAllPermissions()
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }

    /**
     * Override del método can() de Laravel para usar nuestro sistema de permisos
     * Permite usar Gate::allows() y @can directivas con slugs de permisos
     */
    public function can($ability, $arguments = []): bool
    {
        // Super-admin puede todo
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Si es un slug de permiso (formato: modulo.accion.recurso)
        if (is_string($ability) && str_contains($ability, '.')) {
            return $this->hasPermission($ability);
        }

        // Si no, usar la implementación por defecto de Laravel (Policies)
        return parent::can($ability, $arguments);
    }

    public function applications():HasMany
    {
        return $this->hasMany(\Modules\Application\Entities\Application::class, 'applicant_id');
    }
}
