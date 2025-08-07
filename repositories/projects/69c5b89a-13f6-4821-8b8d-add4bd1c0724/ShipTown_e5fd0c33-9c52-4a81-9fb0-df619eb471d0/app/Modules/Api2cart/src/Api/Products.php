<?php

namespace App\Modules\Api2cart\src\Api;

use GuzzleHttp\Exception\GuzzleException;

class Products extends Entity
{
    /**
     * @throws GuzzleException
     */
    public static function getProductList(string $store_key, array $params): RequestResponse
    {
        return Client::GET($store_key, 'product.list.json', $params);
    }

    /**
     * @throws GuzzleException
     */
    public static function assignStore(string $store_key, int $product_id, int $store_id): RequestResponse
    {
        return Client::POST($store_key, 'product.store.assign.json', [
            'product_id' => $product_id,
            'store_id' => $store_id,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public static function productChildItemFind(string $store_key, string $sku): RequestResponse
    {
        return Client::GET($store_key, 'product.child_item.find.json', [
            'find_where' => 'sku',
            'find_value' => $sku,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public static function find($store_key, $params): RequestResponse
    {
        return Client::GET($store_key, 'product.find.json', $params);
    }

    /**
     * @throws GuzzleException
     */
    public static function variantInfo($store_key, $params): RequestResponse
    {
        return Client::GET($store_key, 'product.variant.info.json', $params);
    }

    /**
     * @throws GuzzleException
     */
    public static function add(string $store_key, array $params): RequestResponse
    {
        $final_params = $params;
        data_set($final_params, 'price', data_get($final_params, 'price', 0));

        return Client::POST($store_key, 'product.add.json', $params);
    }
}
