<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Models\OrderComment;
use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use Illuminate\Support\Facades\Log;

class AddOrderCommentAction extends BaseOrderActionAbstract
{
    public function handle(string $options = ''): bool
    {
        Log::debug('Automation Action', [
            'order_number' => $this->order->order_number,
            'class' => class_basename(self::class),
            'comment' => $options,
        ]);

        $comment = new OrderComment;
        $comment->comment = $options;
        $comment->order_id = $this->order->getKey();
        $comment->save();

        return true;
    }
}
