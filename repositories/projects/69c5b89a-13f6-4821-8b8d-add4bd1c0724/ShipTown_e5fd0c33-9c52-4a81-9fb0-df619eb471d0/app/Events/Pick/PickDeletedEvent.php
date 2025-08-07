<?php

namespace App\Events\Pick;

use App\Models\Pick;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PickDeletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Pick $pick;

    public function __construct(Pick $pick)
    {
        $this->pick = $pick;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('channel-name');
    }
}
