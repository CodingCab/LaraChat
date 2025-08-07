<?php

namespace Tests\Browser\Routes\Order\Packsheet;

use App\Models\Order;
use App\Models\OrderProduct;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class PacksheetTabsTest extends DuskTestCase
{
    /**
     * @throws Throwable
     */
    public function testTabsAreVisible(): void
    {
        $order = Order::factory()->create();
        OrderProduct::factory()->create([
            'order_id' => $order->id,
        ]);

        $this->browse(function (Browser $browser) use ($order) {
            $browser->loginAs($this->testUser);
            $browser->visit("/order/packsheet/{$order->id}");
            $browser->waitForText('Packlist');
            $browser->waitForText('Details');
            $browser->waitForText('Addresses');
            $browser->waitForText('Activity');
            $browser->assertSourceMissing('Server Error');
        });
    }
}
