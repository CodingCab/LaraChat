<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\StoreRequest;
use App\Http\Requests\Warehouse\UpdateRequest;
use App\Http\Requests\WarehouseIndexRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Tags\Tag;

class WarehouseController extends Controller
{
    public function index(WarehouseIndexRequest $request): AnonymousResourceCollection
    {
        $query = Warehouse::getSpatieQueryBuilder()
            ->defaultSort('name');

        return WarehouseResource::collection($this->getPaginatedResult($query, 999));
    }

    public function store(StoreRequest $request): WarehouseResource
    {
        $warehouse = Warehouse::create($request->validated());

        return WarehouseResource::make($warehouse);
    }

    public function update(UpdateRequest $request, int $warehouse_id): WarehouseResource
    {
        $warehouse = Warehouse::findOrFail($warehouse_id);

        $attributes = $request->validated();

        $warehouse->update($attributes);

        $tags = data_get($attributes, 'tags', []);

        $tags = collect($tags)->filter()->map(function ($tag) use ($warehouse) {
            $warehouse->attachTag($tag);

            return Tag::findFromString($tag);
        });

        $warehouse->tags()->sync($tags->pluck('id'));

        return WarehouseResource::make($warehouse);
    }

    public function destroy(int $warehouse_id): WarehouseResource
    {
        $warehouse = Warehouse::findOrFail($warehouse_id);

        $warehouse->delete();

        return WarehouseResource::make($warehouse);
    }
}
