<?php

namespace App\Events\OrderProductPick;

use App\Models\OrderProductPick;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderProductPickCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public OrderProductPick $orderProductPick;

    public function __construct(OrderProductPick $orderProductPick)
    {
        $this->orderProductPick = $orderProductPick;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('channel-name');
    }
}
