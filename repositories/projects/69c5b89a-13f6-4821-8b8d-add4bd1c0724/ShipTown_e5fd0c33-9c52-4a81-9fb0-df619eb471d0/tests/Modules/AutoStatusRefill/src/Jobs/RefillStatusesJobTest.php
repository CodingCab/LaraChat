<?php

namespace Tests\Modules\AutoStatusRefill\src\Jobs;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\AutoStatusRefill\src\Jobs\RefillStatusesJob;
use App\Modules\AutoStatusRefill\src\Models\Automation;
use Carbon\Carbon;
use Tests\TestCase;

class RefillStatusesJobTest extends TestCase
{
    public function testExample(): void
    {
        Order::factory()
            ->count(20)
            ->create(['status_code' => 'paid'])
            ->each(function (Order $order) {
                OrderProduct::factory()
                    ->count(rand(1, 5))
                    ->create(['order_id' => $order->id]);
            });

        $automation =Automation::query()->create([
            'from_status_code' => 'paid',
            'to_status_code' => 'picking',
            'desired_order_count' => 10,
            'refill_only_at_0' => false,
        ]);

        RefillStatusesJob::dispatchSync();

        $this->assertDatabaseHas('orders', ['status_code' => 'picking']);
        $this->assertEquals(
            $automation->desired_order_count,
            $automation->current_count_with_status,
        );
    }
}
