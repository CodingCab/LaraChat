<?php

namespace App\Modules\Couriers\ShippyPro\Generic\src\Services;

use App\Exceptions\ShippingServiceException;
use App\Models\Order;
use App\Models\ShippingService;
use App\Modules\Couriers\ShippyPro\ShippyProApi;
use Exception;
use Illuminate\Support\Collection;

class GenericService
{
    /**
     * @throws ShippingServiceException
     */
    public function ship(int $order_id, ?ShippingService $shippingService = null): Collection
    {
        /** @var Order $order */
        $order = Order::query()->with('shippingAddress')->findOrFail($order_id);

        try {
            return ShippyProApi::createShippingLabel($order, [
                'Params' => [
                    'CarrierName' => env('SHIPPY_PRO_GENERIC_CARRIER_NAME', 'Generic'),
                    'CarrierID' => intval(env('SHIPPY_PRO_GENERIC_CARRIER_ID', 4995)),
                    'CarrierService' => env('SHIPPY_PRO_GENERIC_CARRIER_SERVICE', 'Standard'),
                    'parcels' => [
                        [
                            'dimension_unit' => 'CM',
                            'length' => 20,
                            'width' => 20,
                            'height' => 20,
                            'weight' => 1,
                        ],
                    ]
                ]
            ], 'Generic');
        } catch (Exception $exception) {
            throw new ShippingServiceException('Generic (Standard): ' . $exception->getMessage());
        }
    }
}
