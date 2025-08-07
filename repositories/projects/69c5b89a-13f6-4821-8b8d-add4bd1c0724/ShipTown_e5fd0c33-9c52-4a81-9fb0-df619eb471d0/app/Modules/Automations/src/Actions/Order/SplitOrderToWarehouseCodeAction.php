<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Models\Warehouse;
use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use App\Modules\SplitOrder\src\SplitOrderService;
use Illuminate\Support\Facades\Log;

class SplitOrderToWarehouseCodeAction extends BaseOrderActionAbstract
{
    public function handle(string $options = ''): bool
    {
        Log::debug('Automation Action', [
            'order_number' => $this->order->order_number,
            'class' => class_basename(self::class),
            '$options' => $options,
        ]);

        $optionsSeparated = explode(',', $options);

        $splitOrderService = new SplitOrderService;

        $splitOrderService->split(
            $this->order->refresh(),
            Warehouse::whereCode($optionsSeparated[0])->first(),
            $optionsSeparated[1],
        );

        return true;
    }
}
