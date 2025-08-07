<?php

namespace App\Modules\AutoStatusRefill\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Order;
use App\Modules\AutoStatusRefill\src\Models\Automation;

class RefillStatusesJob extends UniqueJob
{
    public function handle(): void
    {
        Automation::query()
            ->get()
            ->each(function (Automation $config) {
                $this->refillStatus($config);
            });
    }

    public function refillStatus(Automation $config): void
    {
        if ($config->refill_only_at_0 && Order::where(['status_code' => $config->to_status_code])->count() > 0) {
            return;
        }

        do {
            $ordersAffected = $this->refill($config);
        } while ($ordersAffected > 0);
    }

    public function refill(Automation $config): int
    {
        logger('Refilling "' . $config->to_status_code . '" status', [
            'desired_order_count' => $config->desired_order_count,
            'from_status_code' => $config->from_status_code,
            'to_status_code' => $config->to_status_code
        ]);

        $currentCount = Order::where(['status_code' => $config->to_status_code])->count();
        $requiredCount = max(0, $config->desired_order_count - $currentCount);

        if ($requiredCount <= 0) {
            return 0;
        }

        $orders = Order::query()
            ->where(['status_code' => $config->from_status_code])
            ->orderBy('order_placed_at')
            ->limit($requiredCount)
            ->get();

        $orders->each(function (Order $order) use ($config) {
            $order->update(['status_code' => $config->to_status_code]);
            $order->log('Moved in batch by AutoBatching module');
        });

        return $orders->count();
    }
}
