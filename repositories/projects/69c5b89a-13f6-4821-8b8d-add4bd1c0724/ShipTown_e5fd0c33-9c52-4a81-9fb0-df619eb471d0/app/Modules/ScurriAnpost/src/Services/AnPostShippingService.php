<?php

namespace App\Modules\ScurriAnpost\src\Services;

use App\Abstracts\ShippingServiceAbstract;
use App\Exceptions\ShippingServiceException;
use App\Models\Order;
use App\Models\ShippingService;
use App\Modules\ScurriAnpost\src\Scurri;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AnPostShippingService extends ShippingServiceAbstract
{
    /**
     * @throws ShippingServiceException
     */
    public function ship(int $order_id, ?ShippingService $shippingService = null): Collection
    {
        /** @var Order $order */
        $order = Order::findOrFail($order_id);

        $orderShipment = Scurri::makeShippingLabel($order);
        $orderShipment->user_id = Auth::id();
        $orderShipment->save();

        activity()
            ->on($order)
            ->by(auth()->user())
            ->log('generated shipping label '.$orderShipment->shipping_number);

        return collect()->add($orderShipment);
    }
}
