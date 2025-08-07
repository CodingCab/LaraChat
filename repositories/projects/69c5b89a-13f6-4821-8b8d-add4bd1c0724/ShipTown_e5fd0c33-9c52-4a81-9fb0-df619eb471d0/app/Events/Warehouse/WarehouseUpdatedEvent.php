<?php

namespace App\Events\Warehouse;

use App\Models\Warehouse;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarehouseUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Warehouse $warehouse;
    public ?string $oldCode;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Warehouse $warehouse, ?string $oldCode = null)
    {
        $this->warehouse = $warehouse;
        $this->oldCode = $oldCode;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('channel-name');
    }
}
