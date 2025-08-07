<?php

namespace App\Modules\Permissions\src\Models;

use App\BaseModel;
use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends BaseModel
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_roles', 'role_id', 'user_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'users_roles_permissions', 'role_id', 'permission_id');
    }

    public function givePermissionTo($permission): void
    {
        $this->permissions()->attach($permission);
    }

    public function hasPermissionTo($permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    public function syncPermissions($permissions): void
    {
        $this->permissions()->sync($permissions);
    }
}
