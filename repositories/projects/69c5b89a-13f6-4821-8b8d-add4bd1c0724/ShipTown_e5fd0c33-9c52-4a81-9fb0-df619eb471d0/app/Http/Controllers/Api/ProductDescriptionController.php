<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductDescription\UpdateOrCreateRequest;
use App\Http\Resources\ProductDescriptionResource;
use App\Models\ProductDescription;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDescriptionController extends Controller
{
    public function store(UpdateOrCreateRequest $request): JsonResource
    {
        $productDescription = ProductDescription::updateOrCreate(
            ['product_id' => $request->product_id, 'language_code' => $request->language_code],
            $request->validated()
        );

        return new ProductDescriptionResource($productDescription);
    }
}
