<?php

namespace App\Modules\Couriers\ShippyPro\DpdPoland\src\Services;

use App\Abstracts\ShippingServiceAbstract;
use App\Exceptions\ShippingServiceException;
use App\Models\Order;
use App\Models\ShippingLabel;
use App\Models\ShippingService;
use App\Models\OrderPayment;
use App\Modules\Couriers\ShippyPro\ShippyProApi;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Collection;

class DpdPolandCashOnDeliveryService extends ShippingServiceAbstract
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
                    'CarrierService' => 'Standard',
                    'CashOnDelivery' => $order->total_shipping + (float) data_get($order, 'orderProductsTotals.total_products_shipped', 0),
                    'CashOnDeliveryCurrency' => 'PLN',
                    'CashOnDeliveryType' => 3,
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

            $shippingNumber = optional($shippingLabels->first())->shipping_number;

            OrderPayment::create([
                'order_id' => $order->getKey(),
                'paid_at' => null,
                'name' => t('Cash on Delivery'),
                'amount' => $order->total_shipping + (float) data_get($order, 'orderProductsTotals.total_products_shipped', 0),
                'additional_fields' => [
                    'id' => (string) Str::uuid(),
                    'shipping_number' => $shippingNumber,
                ],
            ]);

            return $shippingLabels;
        } catch (Exception $exception) {
            throw new ShippingServiceException('DPD Poland (CashOnDelivery): ' . $exception->getMessage());
        }
    }
}
