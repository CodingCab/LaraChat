<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductAliasStoreRequest;
use App\Http\Requests\ProductAliasUpdateRequest;
use App\Http\Resources\ProductAliasResource;
use App\Models\ProductAlias;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductAliasController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $query = ProductAlias::getSpatieQueryBuilder();

        return ProductAliasResource::collection($this->getPaginatedResult($query));
    }

    public function store(ProductAliasStoreRequest $request)
    {
        $productAlias = ProductAlias::query()->updateOrCreate($request->only('alias'), $request->validated());
        $productAlias->refresh();

        return ProductAliasResource::make($productAlias);
    }

    public function update(ProductAliasUpdateRequest $request, ProductAlias $productsAlias)
    {
        $productsAlias->update($request->validated());

        return ProductAliasResource::make($productsAlias);
    }

    public function destroy(ProductAlias $productsAlias)
    {
        $productsAlias->delete();

        return ProductAliasResource::make($productsAlias);
    }
}
