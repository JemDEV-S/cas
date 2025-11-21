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

    public function organizationUnits(): HasMany
    {
        return $this->hasMany(UserOrganizationUnit::class);
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
}
