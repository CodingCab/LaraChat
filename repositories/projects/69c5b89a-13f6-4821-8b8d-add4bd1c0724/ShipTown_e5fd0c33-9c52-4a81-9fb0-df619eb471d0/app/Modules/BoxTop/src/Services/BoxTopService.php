<?php

namespace App\Modules\BoxTop\src\Services;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\ProductAlias;
use App\Modules\BoxTop\src\Api\ApiClient;
use App\Modules\BoxTop\src\Api\ApiResponse;
use App\Modules\BoxTop\src\Exceptions\ProductOutOfStockException;
use App\Modules\BoxTop\src\Models\WarehouseStock;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BoxTopService
{
    public static function postOrder(Order $order): ApiResponse
    {
        self::refreshBoxTopWarehouseStock();

        $data = self::convertToBoxTopFormat($order);

        try {
            return self::apiClient()->createWarehousePick($data);
        } catch (ClientException $exception) {
            Log::error('BoxTop API error', [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ]);
            throw $exception;
        }
    }

    public static function apiClient(): ApiClient
    {
        return new ApiClient;
    }

    private static function convertToBoxTopFormat(Order $order): array
    {
        $pickItems = $order->orderProducts->map(function (OrderProduct $orderProduct) {
            $apiClient = new ApiClient;
            $apiClient->getSkuQuantity($orderProduct->sku_ordered);

            $aliases = $orderProduct->product->aliases()
                ->get('alias')
                ->map(function (ProductAlias $alias) {
                    return $alias->alias;
                })->toArray();

            $possibleSkus = array_merge([$orderProduct->sku_ordered, $orderProduct->product->sku], $aliases);

            /** @var WarehouseStock $warehouseStock */
            $warehouseStock = WarehouseStock::query()
                ->whereIn('SKUNumber', $possibleSkus)
                ->where('Available', '>=', $orderProduct->quantity_ordered)
                ->first();

            if ($warehouseStock === null) {
                throw new ProductOutOfStockException('Insufficient quantity available: '.$orderProduct->sku_ordered);
            }

            return [
                'Warehouse' => $warehouseStock->Warehouse,
                'SKUGroupID' => null,
                'SKUNumber' => $warehouseStock->SKUNumber,
                'SKUName' => $warehouseStock->SKUName,
                'Quantity' => $orderProduct->quantity_ordered,
                'Add1' => '',
                'Add2' => '',
                'Add3' => '',
                'Add4' => '',
                'Add5' => '',
                'Add6' => '',
                'Comments' => '',
            ];
        })->toArray();

        $contactName = Str::substr($order->shippingAddress->full_name.' '.$order->shippingAddress->email, 0, 49);

        return [
            'DeliveryCompanyName' => $order->shippingAddress->company,
            'DeliveryAddress1' => $order->shippingAddress->address1,
            'DeliveryAddress2' => $order->shippingAddress->address2,
            'DeliveryCity' => $order->shippingAddress->city,
            'DeliveryCounty' => $order->shippingAddress->state_code,
            'DeliveryPostCode' => $order->shippingAddress->postcode,
            'DeliveryCountry' => $order->shippingAddress->country_code,
            'DeliveryPhone' => $order->shippingAddress->phone,
            'DeliveryContact' => $contactName,
            'OutboundRef' => $order->order_number,
            'ReleaseDate' => Carbon::today(),
            'DeliveryDate' => '',
            'DeliveryTime' => '',
            'Haulier' => '',
            'PickItems' => $pickItems,
            'BranchID' => env('TEST_BOXTOP_BRANCH_ID', ''),
            'CustomerID' => env('TEST_BOXTOP_CUSTACCNUM', ''),
            'NOP' => 1,
            'Weight' => 1,
            'Cube' => 1,
            'CustRef' => $order->order_number,
            'Remarks' => '',
        ];
    }

    public static function refreshBoxTopWarehouseStock()
    {
        $response = BoxTopService::apiClient()->getStockCheckByWarehouse();
        $stockRecords = collect($response->toArray());

        $stockRecords = $stockRecords->map(function ($record) {
            $record['Attributes'] = json_encode($record['Attributes']);

            return $record;
        });

        WarehouseStock::query()->delete();
        WarehouseStock::query()->insert($stockRecords->toArray());
    }
}
