<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserMeStoreRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserMeController extends Controller
{
    public function index(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function store(UserMeStoreRequest $request): UserResource
    {
        $user = $request->user();
        $validated = $request->validated();

        if (isset($validated['printers'])) {
            $user->printers = array_merge($user->printers ?? [], $validated['printers']);
            unset($validated['printers']);
        }

        $user->update($validated);

        return UserResource::make($request->user()->load('roles'));
    }
}
