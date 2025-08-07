<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\StoreRequest;
use App\Http\Requests\Api\Product\UpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::getSpatieQueryBuilder()
            ->simplePaginate(request()->input('per_page', 10));

        return ProductResource::collection($query);
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $product = Product::query()->updateOrCreate(
            ['sku' => $request->sku],
            $request->validated()
        );

        return response()->json($product, 201);
    }

    public function publish($sku)
    {
        $product = Product::query()->where('sku', $sku)->firstOrFail();

        $product->save();

        $this->respondOK200();
    }

    public function update(UpdateRequest $request, Product $product)
    {
        $data = $request->validated();

        $product->fill($data);
        $product->save();

        return new ProductResource($product);
    }
}
