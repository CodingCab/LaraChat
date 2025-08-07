<?php

namespace App\Modules\Couriers\ShippyPro\InPostPoland\src\Services;

use App\Abstracts\ShippingServiceAbstract;
use App\Exceptions\ShippingServiceException;
use App\Models\Order;
use App\Models\ShippingService;
use App\Modules\Couriers\ShippyPro\ShippyProApi;
use Exception;
use Illuminate\Support\Collection;

class InPostPolandLockerStandardSizeXsService extends ShippingServiceAbstract
{
    public function ship(int $order_id, ?ShippingService $shippingService = null): Collection
    {
        /** @var Order $order */
        $order = Order::query()->with('shippingAddress')->findOrFail($order_id);

        try {
            return ShippyProApi::createShippingLabel($order, [
                'Params' => [
                    'CarrierName' => env('SHIPPY_PRO_INPOST_POLAND_CARRIER_NAME', 'InPostPL'),
                    'CarrierID' => intval($shippingService->connection_details['carrier_id']),
                    'CarrierService' => 'Locker Standard',
                    'parcels' => [
                        [
                            'dimension_unit' => 'CM',
                            'length' => 4,
                            'width' => 23,
                            'height' => 40,
                            'weight' => 0.5,
                        ],
                    ]
                ]
            ], 'InPost');
        } catch (Exception $exception) {
            throw new ShippingServiceException('InPost Poland (Locker Standard - Size XS): ' . $exception->getMessage());
        }
    }
}
