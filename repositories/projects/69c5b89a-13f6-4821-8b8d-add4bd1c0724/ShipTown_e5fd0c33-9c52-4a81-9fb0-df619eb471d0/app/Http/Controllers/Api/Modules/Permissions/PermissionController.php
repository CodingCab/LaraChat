<?php

namespace App\Http\Controllers\Api\Modules\Permissions;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Modules\Reports\src\Models\PermissionsReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Modules\Permissions\src\Models\Role;
use App\Http\Requests\Permission\UpdateRequest;

class PermissionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $resource = PermissionsReport::jsonResource();
        return PermissionResource::collection($resource['data']);
    }

    public function updatePermissions(UpdateRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $attributes['permissions'] = json_decode($attributes['permissions'], true);

        foreach ($attributes['permissions'] as $roleId => $permissions) {
            $role = Role::find($roleId);
            $role->syncPermissions($permissions);
        }

        return response()->json(['message' => 'Permissions updated successfully']);
    }
}
