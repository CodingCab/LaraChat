<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleIndexRequest;
use App\Http\Requests\UserRoleStoreRequest;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Permissions\src\Models\Role;

class UserRoleController extends Controller
{
    public function index(UserRoleIndexRequest $request): ResourceCollection
    {
        return new ResourceCollection(Role::orderBy('name')->get());
    }

    public function store(UserRoleStoreRequest $request)
    {
        $attributes = $request->validated();

        Role::create(['name' => strtolower($attributes['name'])]);

        return response()->json(['message' => 'Role created successfully']);
    }

    public function destroy($id)
    {
        Role::find($id)->delete();

        return response()->json(['message' => 'Role deleted successfully']);
    }
}
