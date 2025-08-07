<?php

namespace App\Events\InventoryMovement;

use App\Models\InventoryMovement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryMovementCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public InventoryMovement $inventoryMovement;

    public function __construct(InventoryMovement $inventoryMovement)
    {
        $this->inventoryMovement = $inventoryMovement;
    }

    public function broadcastOn(): Channel|PrivateChannel
    {
        return new PrivateChannel('channel-name');
    }
}
