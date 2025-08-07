<?php

namespace App\Events\OrderProduct;

use App\Models\OrderProductShipment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderProductShipmentCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OrderProductShipment $orderProductShipment;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OrderProductShipment $orderProductShipment)
    {
        $this->orderProductShipment = $orderProductShipment;
    }
}
