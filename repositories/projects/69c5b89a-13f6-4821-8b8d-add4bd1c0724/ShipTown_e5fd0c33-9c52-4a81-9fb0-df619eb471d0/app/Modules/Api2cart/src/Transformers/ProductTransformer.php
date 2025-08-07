<?php

namespace App\Modules\Api2cart\src\Transformers;

use App\Modules\Api2cart\src\Models\Api2cartProductLink;
use App\Modules\Api2cart\src\Services\Api2cartService;

class ProductTransformer
{
    public static function toApi2cartPayload(Api2cartProductLink $api2cartProductLink): array
    {
        $data = collect();

        $data = $data->merge(self::getBasicData($api2cartProductLink));
        $data = $data->merge(self::getMagentoStoreId($api2cartProductLink));
        $data = $data->merge(self::getInventoryData($api2cartProductLink));
        $data = $data->merge(self::getPricingData($api2cartProductLink));

        return $data->toArray();
    }

    private static function getBasicData(Api2cartProductLink $productLink): array
    {
        return [
            'id' => $productLink->api2cart_product_id,
            'product_id' => $productLink->product->getKey(),
            'sku' => $productLink->product->sku,
            'name' => $productLink->product->name,
            'description' => $productLink->product->name,
        ];
    }

    private static function getMagentoStoreId(Api2cartProductLink $productLink): array
    {
        return [
            'store_id' => $productLink->api2cartConnection->magento_store_id ?? 0,
        ];
    }

    private static function getInventoryData(Api2cartProductLink $api2cartProductLink): array
    {
        $inventory_source_warehouse_tag = $api2cartProductLink->api2cartConnection->inventory_source_warehouse_tag;

        $sum = $api2cartProductLink->product->inventory()
            ->withWarehouseTags($inventory_source_warehouse_tag)
            ->sum('quantity_available');

        $quantity_available = floor($sum ?? 0);

        return [
            'quantity' => $quantity_available > 0 ? $quantity_available : 0,
            'in_stock' => $quantity_available > 0 ? 'True' : 'False',
        ];
    }

    private static function getPricingData(Api2cartProductLink $api2cartProductLink): array
    {
        if ($api2cartProductLink->api2cartConnection->pricing_source_warehouse_id === null) {
            return [];
        }

        $productPrice = $api2cartProductLink->product
            ->prices()
            ->where(['warehouse_id' => $api2cartProductLink->api2cartConnection->pricing_source_warehouse_id])
            ->first();

        return [
            'price' => $productPrice->price,
            'special_price' => $productPrice->sale_price,
            'sprice_create' => Api2cartService::formatDateForApi2cart($productPrice->sale_price_start_date),
            'sprice_expire' => Api2cartService::formatDateForApi2cart($productPrice->sale_price_end_date),
        ];
    }
}
