<?php

namespace App\Modules\Permissions\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Permissions\src\Models\Permission;
use App\Modules\Permissions\src\Models\Role;
use Illuminate\Support\Facades\Artisan;

class CreateRoutePermissionsJob extends UniqueJob
{
    public function handle(): void
    {
        Artisan::call('route:list --json --env=production');

        $artisanOutput = json_decode(Artisan::output());

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        collect($artisanOutput)
            ->each(function ($route) use ($adminRole, $userRole) {
                if ($route->name === null || str_contains($route->name, 'debugbar') || str_contains($route->name, 'dusk') || str_contains($route->name, 'verify')) {
                    return true;
                }

                $permission = Permission::firstOrCreate([
                    'name' => $route->name
                ]);

                if (!str_contains($route->name, 'settings') && !$userRole->hasPermissionTo($permission->name)) {
                    $userRole->givePermissionTo($permission);
                }

                if (!$adminRole->hasPermissionTo($permission->name)) {
                    $adminRole->givePermissionTo($permission);
                }
            });
    }
}
