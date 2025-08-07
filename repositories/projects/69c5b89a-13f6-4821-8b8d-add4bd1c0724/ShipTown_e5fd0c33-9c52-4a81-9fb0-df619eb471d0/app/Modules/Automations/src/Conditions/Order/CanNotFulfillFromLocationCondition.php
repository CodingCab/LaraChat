<?php

namespace App\Modules\Automations\src\Conditions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderConditionAbstract;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;

class CanNotFulfillFromLocationCondition extends BaseOrderConditionAbstract
{
    public function isValid(?string $expected_value = ''): bool
    {
        if ($expected_value === '0') {
            $expected_value = null;
        }

        $result = OrderService::canNotFulfill($this->order, $expected_value);

        Log::debug('Automation condition', [
            'order_number' => $this->order->order_number,
            'result' => $result,
            'class' => class_basename(self::class),
            'location_id' => $expected_value,
        ]);

        return $result;
    }
}
