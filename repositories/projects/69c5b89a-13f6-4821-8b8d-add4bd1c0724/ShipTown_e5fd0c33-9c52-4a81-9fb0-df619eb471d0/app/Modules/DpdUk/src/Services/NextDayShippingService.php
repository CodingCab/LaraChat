<?php

namespace App\Modules\DpdUk\src\Services;

use App\Abstracts\ShippingServiceAbstract;
use App\Exceptions\ShippingServiceException;
use App\Models\Order;
use App\Models\ShippingService;
use Exception;
use Illuminate\Support\Collection;

class NextDayShippingService extends ShippingServiceAbstract
{
    /**
     * @throws Exception
     */
    public function ship(int $order_id, ?ShippingService $shippingService = null): Collection
    {
        /** @var Order $order */
        $order = Order::findOrFail($order_id);

        try {
            $shipment = (new DpdUkService)->createShippingLabel($order);
        } catch (Exception $exception) {
            throw new ShippingServiceException('DPD UK: '.$exception->getMessage());
        }

        return collect()->add($shipment);
    }
}
