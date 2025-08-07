<?php

namespace App\Modules\Permissions\src\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RolePermission extends Pivot
{
    protected $table = 'users_roles_permissions';

    protected $fillable = [
        'role_id',
        'permission_id',
    ];
}
