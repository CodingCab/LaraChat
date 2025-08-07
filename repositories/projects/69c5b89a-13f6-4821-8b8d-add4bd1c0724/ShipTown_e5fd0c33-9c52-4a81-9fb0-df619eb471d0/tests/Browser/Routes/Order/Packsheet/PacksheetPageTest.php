<?php

namespace Tests\Browser\Routes\Order\Packsheet;

use App\Models\Order;
use App\Modules\Inventory\src\Jobs\RecalculateInventoryRecordsJob;
use Database\Seeders\AssemblyOrderSeeder;
use Database\Seeders\FullStocktakeSeeder;
use Database\Seeders\PaidTodayOrdersFromStockSeeder;
use Database\Seeders\PaidTodaysOrdersFromStockSeeder;
use Database\Seeders\RabenGroupSeeder;
use Tests\DuskTestCase;

class PacksheetPageTest extends DuskTestCase
{
    private Order $order;

    public function testBasicScenarios(): void
    {
        $this->visit('/fulfillment-dashboard');

        $this->startRecording('Packlist - How to pack and ship an order?');

        $this->say('Hi Guys! I will demonstrate quickly how to pack an order using our Packlist module');
        $this->say('Lets start from the top menu click on Tools > Packlist');
        $this->clickButton('#tools_link');
        $this->clickButton('#packlist_link');

        $this->pause();

        $this->clickButton('@startAutopilotButton');

        $this->say('Scan product barcode or swipe right to mark as shipped');

        $this->pause();

        $this->waitUntilMissingText('No products found');
        $this->browser()->assertSourceMissing('snotify-error');
        $this->pause();
        $this->browser()->waitForText($this->order->order_number);

        do {
            $randomOrderProduct = $this->order->orderProducts()->where('quantity_to_ship', '>', 0)->first();

            if ($randomOrderProduct !== null) {
                $this->type('@barcode-input-field', $randomOrderProduct->sku_ordered, true);
                $this->browser()->waitForText('1 x shipped');
            }
        } while ($randomOrderProduct !== null);

        $this->say('All products have been packed, and your shipping label has been automatically printed');
        $this->say('After 5 seconds you will be served with next order to pack');
        $this->say('By default, orders are prioritized by the date when the orders was placed so you get the oldest orders first. But this is something you can easily change in the settings');

        $this->pause(4);

        $this->stopRecording();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUser->update(['ask_for_shipping_number' => false]);

        $this->seed(RabenGroupSeeder::class);
        $this->seed(AssemblyOrderSeeder::class);
        $this->seed(FullStocktakeSeeder::class);
        $this->seed(PaidTodaysOrdersFromStockSeeder::class);

        $this->order = Order::query()->where('order_number', '#1232764-ASSEMBLY')->first();

        RecalculateInventoryRecordsJob::dispatchSync();
    }
}
