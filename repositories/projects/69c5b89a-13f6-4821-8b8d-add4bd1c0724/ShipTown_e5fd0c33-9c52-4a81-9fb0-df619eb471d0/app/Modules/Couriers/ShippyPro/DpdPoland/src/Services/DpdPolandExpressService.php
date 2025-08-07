<?php

namespace App\Modules\Couriers\ShippyPro\DpdPoland\src\Services;

use App\Abstracts\ShippingServiceAbstract;
use App\Exceptions\ShippingServiceException;
use App\Models\Order;
use App\Models\ShippingLabel;
use App\Models\ShippingService;
use App\Modules\Couriers\ShippyPro\ShippyProApi;
use Exception;
use Illuminate\Support\Collection;

class DpdPolandExpressService extends ShippingServiceAbstract
{
    public function ship(int $order_id, ?ShippingService $shippingService = null): Collection
    {
        /** @var Order $order */
        $order = Order::query()->with('shippingAddress')->findOrFail($order_id);

        try {
            $shippingLabels = ShippyProApi::createShippingLabel($order, [
                'Params' => [
                    'CarrierName' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_NAME', 'DpdPoland'),
                    'CarrierID' => intval($shippingService->connection_details['carrier_id']),
                    'CarrierService' => 'Express',
                    'parcels' => [
                        [
                            'dimension_unit' => 'CM',
                            'length' => 20,
                            'width' => 20,
                            'height' => 20,
                            'weight' => 0.1,
                        ],
                    ],
                ],
            ]);

            $shippingLabels->each(function (ShippingLabel $label) {
                $label->tracking_url = 'https://tracktrace.dpd.com.pl/parcelDetails?typ=1&p1='.$label->shipping_number;
                $label->save();
            });

            return $shippingLabels;
        } catch (Exception $exception) {
            throw new ShippingServiceException('DPD Poland (Express): ' . $exception->getMessage());
        }
    }
}
