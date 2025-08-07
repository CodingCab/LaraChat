<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Eloquent\Collection;

class PricingService
{
    public static function updateProductPrice(string $skuOrAlias, float $price, ?int $warehouseId = null): Collection
    {
        /** @var Product $product */
        $product = ProductService::find($skuOrAlias);

        $updatedProductPrices = $product->prices()
            ->when($warehouseId, function ($query, $warehouseId) {
                $query->where(['warehouse_id' => $warehouseId]);
            })
            ->get();

        $updatedProductPrices->each(function (ProductPrice $productPrice) use ($price) {
            $productPrice->update(['price' => $price]);
        });

        return $updatedProductPrices;
    }
}
