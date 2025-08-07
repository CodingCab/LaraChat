<?php

namespace App\Modules\Api2cart\src\Services;

use App\Modules\Api2cart\src\Api\Client;
use App\Modules\Api2cart\src\Api\Products;
use App\Modules\Api2cart\src\Api\RequestResponse;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartProductLink;
use App\Modules\Api2cart\src\Transformers\ProductTransformer;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;

class Api2cartService
{
    private const PRODUCT_ALLOWED_KEYS = [
        'id',
        'sku',
        'model',
        'name',
        'description',
        'price',
        'special_price',
        'sprice_create',
        'sprice_expire',
        'quantity',
        'in_stock',
        'store_id',
        'warehouse_id',
    ];

    private const PRODUCT_DONT_UPDATE_KEYS = [
        'model',
        'sku',
        'name',
        'description',
    ];

    /**
     * @throws GuzzleException
     */
    public static function getVariantID(string $store_key, string $sku): ?int
    {
        try {
            $response = Products::productChildItemFind($store_key, $sku);
        } catch (Exception $exception) {
            return null;
        }

        if (isset($response) && ($response->isNotSuccess())) {
            return null;
        }

        return $response->getResult()['children'][0]['id'];
    }

    /**
     * @throws GuzzleException
     */
    public static function createSimpleProduct(string $store_key, array $product_data): RequestResponse
    {
        $fields = [
            'sku',
            'model',
            'name',
            'description',
            'price',
            'quantity',
        ];

        $product = Arr::only($product_data, $fields);

        if (! Arr::has($product_data, 'model')) {
            $product['model'] = $product_data['sku'];
        }

        // disable new products
        $product['available_for_view'] = false;
        $product['available_for_sale'] = false;

        return Products::add($store_key, $product);
    }

    /**
     * @throws GuzzleException
     */
    public static function updateSimpleProduct(string $store_key, array $data): RequestResponse
    {
        $product = collect($data)
            ->only(self::PRODUCT_ALLOWED_KEYS)
            ->except(self::PRODUCT_DONT_UPDATE_KEYS)
            ->merge([
                'reindex' => 'False',
                'clear_cache' => 'False',
            ])
            ->toArray();

        return Client::POST($store_key, 'product.update.json', $product);
    }

    /**
     * This will only update variant product, will not update simple product.
     *
     * @throws GuzzleException
     */
    public static function updateVariant(string $store_key, array $data): RequestResponse
    {
        $properties = collect($data)
            ->only(self::PRODUCT_ALLOWED_KEYS)
            ->except(self::PRODUCT_DONT_UPDATE_KEYS)
            ->merge([
                'reindex' => 'False',
                'clear_cache' => 'False',
            ])
            ->toArray();

        return Client::POST($store_key, 'product.variant.update.json', $properties);
    }

    /**
     * @throws GuzzleException
     */
    private static function productUpdateOrCreate(Api2cartProductLink $product_link): RequestResponse
    {
        $store_key = $product_link->api2cartConnection->bridge_api_key;
        $product_data = ProductTransformer::toApi2cartPayload($product_link);

        $properties = array_merge($product_data, ['id' => $product_link->api2cart_product_id]);

        switch ($product_link->api2cart_product_type) {
            case 'variant':
                $response = self::updateVariant($store_key, $properties);
                switch ($response->getReturnCode()) {
                    case RequestResponse::RETURN_CODE_MODEL_NOT_FOUND:
                        $product_link->update([
                            'api2cart_product_type' => null,
                            'api2cart_product_id' => null,
                        ]);
                }

                return $response;
            default:
                $response = self::updateSimpleProduct($store_key, $properties);
                switch ($response->getReturnCode()) {
                    case RequestResponse::RETURN_CODE_MODEL_NOT_FOUND:
                        // product might not be assigned to store, we try it
                        if (data_get($properties, 'store_id')) {
                            Products::assignStore(
                                $store_key,
                                data_get($properties, 'id'),
                                data_get($properties, 'store_id')
                            );
                        }

                        $product_link->update([
                            'api2cart_product_type' => null,
                            'api2cart_product_id' => null,
                        ]);
                }

                return $response;
        }
    }

    /**
     * @throws GuzzleException
     */
    public static function getSimpleProductInfoByID(Api2cartConnection $conn, int $id, ?array $fields = null): ?array
    {
        if (empty($id)) {
            return null;
        }

        $params = [
            'id' => $id,
            'params' => implode(
                ',',
                $fields ?? [
                    'id',
                    'type',
                    'model',
                    'u_model',
                    'sku',
                    'u_sku',
                    'price',
                    'special_price',
                    'stores_ids',
                    'manage_stock',
                    'quantity',
                    'inventory',
                ]
            ),
        ];

        if ($conn->magento_store_id !== null) {
            $params['store_id'] = $conn->magento_store_id;
        }

        $response = Client::GET($conn->bridge_api_key, 'product.info.json', $params);

        if ($response->isNotSuccess()) {
            return null;
        }

        $product = $response->getResult();

        return self::transformProduct($product, $conn);
    }

    /**
     * @throws GuzzleException
     */
    public static function getProductsList(Api2cartConnection $conn, ?array $product_ids = null, ?array $params = null): RequestResponse
    {
        $query = [
            'params' => implode(
                ',',
                $params ?? [
                    'id',
                    'type',
                    'model',
                    'u_model',
                    'sku',
                    'u_sku',
                    'price',
                    'special_price',
                    'stores_ids',
                    'manage_stock',
                    'quantity',
                    'inventory',
                ]
            ),
        ];

        if ($product_ids) {
            $query['product_ids'] = implode(',', $product_ids);
            $query['count'] = count($product_ids);
        }

        if ($conn->magento_store_id !== null) {
            $query['store_id'] = $conn->magento_store_id;
        }

        if ($params) {
            $query = array_merge($query, $params);
        }

        return Client::GET($conn->bridge_api_key, 'product.list.json', $query);
    }

    /**
     * @throws GuzzleException
     */
    public static function getSimpleProductInfo(Api2cartConnection $conn, string $sku, ?array $fields = null): ?array
    {
        $product_id = self::getSimpleProductID($conn->bridge_api_key, $sku);

        return self::getSimpleProductInfoByID($conn, $product_id, $fields);
    }

    /**
     * @throws GuzzleException
     */
    public static function getVariantInfoByID(Api2cartConnection $connection, int $id, ?array $fields = null): ?array
    {
        if (empty($id)) {
            return null;
        }

        $params = [
            'id' => $id,
            'params' => implode(
                ',',
                $fields ?? [
                    'id',
                    'model',
                    'type',
                    'u_model',
                    'sku',
                    'u_sku',
                    'price',
                    'special_price',
                    'stores_ids',
                    'quantity',
                    'inventory',
                    'group_items',
                ]
            ),
        ];

        if ($connection->magento_store_id !== null) {
            $params['store_id'] = $connection->magento_store_id;
        }

        $response = Products::variantInfo($connection->bridge_api_key, $params);

        if ($response->isNotSuccess()) {
            return null;
        }

        $variant = $response->getResult()['variant'];

        return self::transformProduct($variant, $connection);
    }

    /**
     * @throws GuzzleException
     */
    public static function getVariantInfo(Api2cartConnection $connection, string $sku, ?array $fields = null): ?array
    {
        $variant_id = self::getVariantID($connection->bridge_api_key, $sku);

        return self::getVariantInfoByID($connection, $variant_id, $fields);
    }

    /**
     * @throws GuzzleException
     */
    public static function getProductInfo(Api2cartConnection $connection, string $sku, ?array $fields = null): ?array
    {
        return self::getSimpleProductInfo($connection, $sku, $fields)
            ?? self::getVariantInfo($connection, $sku, $fields);
    }

    /**
     * @throws GuzzleException
     */
    public static function getSimpleProductID(string $store_key, string $sku): ?int
    {
        $response = Products::find($store_key, [
            'find_where' => 'model',
            'find_value' => $sku,
            'store_id' => 0,
        ]);

        if (isset($response) && ($response->isNotSuccess())) {
            return null;
        }

        return $response->getResult()['product'][0]['id'];
    }

    /**
     * @throws GuzzleException
     */
    public static function getProductTypeAndId(string $store_key, string $sku): array
    {
        // try to find simple product id
        $product_id = self::getSimpleProductID($store_key, $sku);

        if ($product_id) {
            return [
                'type' => 'simple',
                'id' => $product_id,
            ];
        }

        // try to get variant if simple product does not exist
        $variant_id = self::getVariantID($store_key, $sku);

        if (! empty($variant_id)) {
            return [
                'type' => 'variant',
                'id' => $variant_id,
            ];
        }

        // returning null if nothing found
        return [
            'type' => null,
            'id' => null,
        ];
    }

    public static function getQuantity(array $product, ?string $warehouse_id = null): int
    {
        if (is_null($warehouse_id)) {
            return $product['quantity'];
        }

        $inventories = collect($product['inventory'])
            ->filter(function ($warehouse_inventory) use ($warehouse_id) {
                return $warehouse_inventory['warehouse_id'] === $warehouse_id;
            });

        if ($inventories->isEmpty()) {
            return $product['quantity'];
        }

        return $inventories->first()['quantity'];
    }

    public static function transformProduct($product, Api2cartConnection $connection): array
    {
        $product['sku'] = empty($product['u_sku']) ? $product['u_model'] : $product['u_sku'];
        $product['model'] = $product['u_model'];
        $product['quantity'] = self::getQuantity($product, $connection->magento_warehouse_id);

        $created_at = $product['special_price']['created_at'];
        $product['sprice_create'] = empty($created_at) ? '1900-01-01 00:00:00' : $created_at['value'];
        $product['sprice_create'] = Carbon::createFromTimeString($product['sprice_create'])->format('Y-m-d H:i:s');

        $expired_at = $product['special_price']['expired_at'];
        $product['sprice_expire'] = empty($expired_at) ? '1900-01-01 00:00:00' : $expired_at['value'];
        $product['sprice_expire'] = Carbon::createFromTimeString($product['sprice_expire'])->format('Y-m-d H:i:s');

        $product['special_price'] = $product['special_price']['value'];

        return $product;
    }

    public static function updateSku(Api2cartProductLink $productLink): bool
    {
        try {
            $requestResponse = self::productUpdateOrCreate($productLink);

            if ($requestResponse->isSuccess()) {
                return true;
            }

            $productLink->product->log('eCommerce: Sync failed', [
                'return_code' => $requestResponse->getReturnCode(),
                'message' => $requestResponse->getReturnMessage(),
            ]);
        } catch (ConnectException $exception) {
            $productLink->product->log('eCommerce: Connection timeout, retry scheduled');

            return false;
        } catch (GuzzleException $exception) {
            report($exception);
            $productLink->product->log('eCommerce: Sync failed, see logs for more details');

            return false;
        }

        return false;
    }

    public static function formatDateForApi2cart($date): string
    {
        $carbon_date = new \Illuminate\Support\Carbon($date ?? '2000-01-01 00:00:00');

        if ($carbon_date->year < 2000) {
            return '2000-01-01 00:00:00';
        }

        return $carbon_date->toDateTimeString();
    }
}
