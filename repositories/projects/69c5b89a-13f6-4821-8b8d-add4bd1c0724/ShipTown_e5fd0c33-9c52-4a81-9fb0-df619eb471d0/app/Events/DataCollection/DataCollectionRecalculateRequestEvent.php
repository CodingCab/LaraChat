<?php

namespace App\Events\DataCollection;

use App\Models\DataCollection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataCollectionRecalculateRequestEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public DataCollection $dataCollection;

    public function __construct(DataCollection $dataCollection)
    {
        $this->dataCollection = $dataCollection;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('channel-name');
    }
}
