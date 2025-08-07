<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AssemblyProducts\AssemblyProductElementsStoreRequest;
use App\Http\Requests\Api\AssemblyProducts\AssemblyProductElementsUpdateRequest;
use App\Http\Resources\AssemblyProductsElementResource;
use App\Models\Product;
use App\Modules\AssemblyProducts\src\Models\AssemblyProductsElement;

class AssemblyProductElementsController extends Controller
{
    public function store(AssemblyProductElementsStoreRequest $request): AssemblyProductsElementResource
    {
        $attributes = $request->validated();

        $mainProduct = Product::findOrFail($attributes['product_id']);
        $elementProduct = Product::query()->where('sku', $attributes['sku'])->firstOrFail();

        $assemblyProductElement = AssemblyProductsElement::create([
            'assembly_product_id' => $mainProduct->id,
            'simple_product_id' => $elementProduct->id,
            'required_quantity' => 1
        ]);

        $assemblyProductElement->load('simpleProduct');
        return new AssemblyProductsElementResource($assemblyProductElement);
    }

    public function update(AssemblyProductElementsUpdateRequest $request, int $id): AssemblyProductsElementResource
    {
        $attributes = $request->validated();

        $assemblyProductElement = AssemblyProductsElement::findOrFail($id);
        $assemblyProductElement->update([
            'required_quantity' => $attributes['quantity']
        ]);

        $assemblyProductElement->load('simpleProduct');
        return new AssemblyProductsElementResource($assemblyProductElement);
    }

    public function destroy(int $id): void
    {
        $assemblyProductElement = AssemblyProductsElement::findOrFail($id);
        $assemblyProductElement->delete();
    }
}
