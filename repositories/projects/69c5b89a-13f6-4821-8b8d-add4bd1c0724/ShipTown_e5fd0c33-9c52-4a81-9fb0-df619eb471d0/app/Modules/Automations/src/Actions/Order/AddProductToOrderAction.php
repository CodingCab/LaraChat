<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use Illuminate\Support\Facades\Log;

class AddProductToOrderAction extends BaseOrderActionAbstract
{
    public function handle(string $options = ''): bool
    {
        Log::debug('Automation Action', [
            'order_number' => $this->order->order_number,
            'class' => class_basename(self::class),
            '$options' => $options,
        ]);

        return true;
    }
}
