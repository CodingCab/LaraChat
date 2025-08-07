<?php

namespace App\Http\Middleware;

use App\Modules\Permissions\src\PermissionsModuleServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckApiPermissions
{
    public function handle(Request $request, Closure $next): Response
    {
        if (PermissionsModuleServiceProvider::isEnabled() && $request->user()) {
            $routeName = $request->route()->getName();

            $permissionExists = DB::selectOne("SELECT id FROM permissions WHERE name = ? LIMIT 1", [$routeName]);

            if (!$permissionExists) {
                return $next($request);
            }

            $hasPermission = DB::selectOne("
                SELECT 1
                FROM users_roles_permissions urp
                JOIN users_roles ur ON ur.role_id = urp.role_id
                WHERE urp.permission_id = ? AND ur.user_id = ?
                LIMIT 1
            ", [$permissionExists->id, $request->user()->id]);

            if ($hasPermission) {
                return $next($request);
            }

            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
