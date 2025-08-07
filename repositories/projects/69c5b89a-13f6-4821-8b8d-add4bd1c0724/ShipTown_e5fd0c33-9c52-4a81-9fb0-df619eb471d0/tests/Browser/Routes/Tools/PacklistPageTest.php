<?php

namespace Tests\Browser\Routes\Tools;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Warehouse;
use App\Modules\AddressLabel\src\AddressLabelServiceProvider;
use App\Services\InventoryService;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Tests\DuskTestCase;
use Throwable;

class PacklistPageTest extends DuskTestCase
{
    private string $uri = '/tools/packlist';

    /**
     * @throws Throwable
     */
    public function testPage()
    {
        $this->visit('/fulfillment-dashboard');
        $browser = $this->browser();

        $this->startRecording('How to pack an order ?');

        $this->say('Do you want to see how to pack an order?');
        $this->say('Let\'s start by clicking on the Tools and then Packlist in the top menu.');

        $this->pause()
            ->clickButton('#tools_link')
            ->pause()
            ->clickButton('#packlist_link');

        $this->say('You can see the list of orders groups that are ready to be packed.
            Let\'s click on the PAID orders to start packing');

        $browser
            ->pause($this->shortDelay)
            ->waitForText('Status: paid')
            ->assertSee('Status: paid')
            ->clickLink('Status: paid')
            ->pause($this->shortDelay);

        $this->say('Right away, you will be shown first order to pack.
            You can see the product SKU input field on the top of the screen, where you can scan the product code or enter the barcode manually.
            Let\'s scan the first product code');

        $this->nextProductToPack()
            ->first(function (OrderProduct $orderProduct) use ($browser) {
                $this->pause(1);
                $browser->screenshot('packlist_page_test_1');
                $browser->waitForText($orderProduct->product->sku)
                    ->assertSee($orderProduct->product->sku);
                $browser->pauseWhenRecording();
                // Wait for page to be fully loaded and input field to be ready
                $browser->pause($this->mediumDelay);
                $this->typeAndEnter($orderProduct->product->sku);
            });

        $this->say('You can see the product has been marked as shipped. Let\'s go ahead and scan the rest of the products');

        while ($this->orderHasProductsToPack()) {
            $this->nextProductToPack()
                ->first(function (OrderProduct $orderProduct) use ($browser) {
                    $this->pause();
                    $browser->waitForText($orderProduct->product->sku)
                        ->assertSee($orderProduct->product->sku);
                    $browser->shortPause();
                    $browser->pauseWhenRecording();
                    // Wait for page to be fully loaded and input field to be ready
                    $this->pause();
                    $this->typeAndEnter($orderProduct->product->sku);
                });
        }

        $this->pause(1);
//        $browser->typeAndEnter('CB100023444');

        $this->say('You have packed all the products in the order, courier label is automatically generated and printed for you.
            The order has been marked as shipped and customer has been sent a notification email with the tracking number.
            You can see that next order is already displayed for you to pack so your staff can continue packing the orders quickly and efficiently, without the need to think and decide what to pack next.');

        $this->pause(5);
    }

    function orderHasProductsToPack(): bool
    {
        return OrderProduct::query()
            ->where(['order_id' => $this->order->getKey()])
            ->where('quantity_to_ship', '>', 0)
            ->exists();
    }

    public function nextProductToPack(): Collection
    {
        return $this->order->orderProducts()
            ->where('quantity_to_ship', '>', 0)
            ->leftJoin('inventory', function ($join) {
                $join->on('orders_products.product_id', '=', 'inventory.product_id');
                $join->on('inventory.warehouse_id', '=', DB::raw($this->user->warehouse_id));
            })
            ->orderBy('inventory.shelve_location')
            ->limit(1)
            ->get();
    }

    protected function setUp(): void
    {
        parent::setUp();

        AddressLabelServiceProvider::enableModule();

        $this->warehouse = Warehouse::factory()->create();
        $this->user = User::factory()->create(['warehouse_id' => $this->warehouse->getKey(), 'warehouse_code' => $this->warehouse->code, 'ask_for_shipping_number' => false]);

        $this->order = Order::factory()->create(['status_code' => 'paid', 'label_template' => 'address_label']);

        $product1 = OrderProduct::factory()->create(['order_id' => $this->order->getKey(), 'quantity_ordered' => 1]);
        $inventory1 = Inventory::query()->where(['product_id' => $product1->product_id, 'warehouse_id' => $this->warehouse->getKey()])->first();
        $inventory1->update(['shelve_location' => 'D4']);
        InventoryService::stocktake($inventory1, 5);

        $product2 = OrderProduct::factory()->create(['order_id' => $this->order->getKey(), 'quantity_ordered' => 3]);
        $inventory2 = Inventory::query()->where(['product_id' => $product2->product_id, 'warehouse_id' => $this->warehouse->getKey()])->first();
        $inventory2->update(['shelve_location' => 'H2']);
        InventoryService::stocktake($inventory2, 18);

        $this->order = Order::factory()->create(['status_code' => 'paid', 'label_template' => 'address_label']);

        $product1 = OrderProduct::factory()->create(['order_id' => $this->order->getKey(), 'quantity_ordered' => 1]);
        $inventory1 = Inventory::query()->where(['product_id' => $product1->product_id, 'warehouse_id' => $this->warehouse->getKey()])->first();
        $inventory1->update(['shelve_location' => 'A1']);
        InventoryService::stocktake($inventory1, 7);

        $product2 = OrderProduct::factory()->create(['order_id' => $this->order->getKey(), 'quantity_ordered' => 3]);
        $inventory2 = Inventory::query()->where(['product_id' => $product2->product_id, 'warehouse_id' => $this->warehouse->getKey()])->first();
        $inventory2->update(['shelve_location' => 'C7']);
        InventoryService::stocktake($inventory2, 11);
    }
}
