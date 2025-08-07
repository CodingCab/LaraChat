<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::getSpatieQueryBuilder();

        return InventoryResource::collection($this->getPaginatedResult($query));
    }

    public function store(StoreInventoryRequest $request): AnonymousResourceCollection
    {
        /** @var Inventory $inventory */
        $inventory = Inventory::query()
            ->findOrFail($request->validated()['id']);

        $user = $request->user();

        if ($user && $user->warehouse_code) {
            abort_if(
                $inventory->warehouse_code !== $user->warehouse_code,
                403,
                'Forbidden'
            );
        }

        $inventory->update($request->validated());

        return InventoryResource::collection(collect([$inventory->refresh()]));
    }
}
