<?php

namespace App\Modules\Permissions\src\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRole extends Pivot
{
    protected $table = 'users_roles';

    protected $fillable = [
        'user_id',
        'role_id',
    ];
}
