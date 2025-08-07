<?php

namespace Tests\Browser;

use App\Models\Configuration;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductPicture;
use App\Models\Warehouse;
use App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider;
use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use App\User;
use Facebook\WebDriver\Exception\ElementClickInterceptedException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PagesWalkthroughTest extends DuskTestCase
{
    private Order $order;

    private User $user;

    private Product $product1;

    private Product $product2;

    public function testExample(): void
    {
        $browser = $this->browser();

        $this->visit('login');
        $this->startRecording('MyShipTown Feature Walkthrough');

        $this->say('Lets visit myshiptown.com and log in');
        $this->pause();
//
//        $this->visitAndInspect('products/inventory')->screenshot();
//        $this->visitAndInspect('products/transfers-in')->screenshot();
//        $this->visitAndInspect('products/transfers-out')->screenshot();
//        $this->visitAndInspect('products/purchase-orders')->screenshot();
//        $this->visitAndInspect('products/transactions')->screenshot();
//        $this->visitAndInspect('products/stocktaking')->screenshot();
//        $this->visitAndInspect('orders')->screenshot();
//        $this->visitAndInspect('tools/picklist?step=select')->screenshot();
//        $this->visitAndInspect('tools/picklist?order.status_code=paid')->screenshot();
//        $this->visitAndInspect('tools/packlist?step=select')->screenshot();
//        $this->visitAndInspect('tools/packlist?status=paid')->screenshot();
//        $this->visitAndInspect('tools/restocking')->screenshot();
//        $this->visitAndInspect('tools/data-collector')->screenshot();
//        $this->visitAndInspect('tools/data-collector/transaction')->screenshot();
//        $this->visitAndInspect('tools/shelf-labels')->screenshot();
//        $this->visitAndInspect('reports/inventory-dashboard')->screenshot();
//        $this->visitAndInspect('reports/orders-dashboard')->screenshot();
//        $this->visitAndInspect('reports/inventory-movements')->screenshot();
//        $this->visitAndInspect('reports/picks')->screenshot();
//        $this->visitAndInspect('/inventory-dashboard')->screenshot();
//        $this->visitAndInspect('/fulfillment-dashboard')->screenshot();
//        $this->visitAndInspect('/fulfillment-statistics?between_dates=-7days,now')->screenshot();
//        $this->visitAndInspect('/reports/orders?sort=-order_placed_at')->screenshot();
//        $this->visitAndInspect('/reports/picks?sort=-picked_at')->screenshot();
//        $this->visitAndInspect('/reports/shipments?sort=-created_at')->screenshot();
//        $this->visitAndInspect('/reports/order-products?sort=-order_placed_at')->screenshot();
//        $this->visitAndInspect('/reports/inventory?filter[warehouse_code]=WH01&sort=-quantity')->screenshot();
//        $this->visitAndInspect('/reports/inventory-movements?filter[warehouse_code]=WH01&sort=-occurred_at,-sequence_number')->screenshot();
//        $this->visitAndInspect('/reports/inventory-transfers?filter[warehouse_code]=WH01&sort=-created_at')->screenshot();
//        $this->visitAndInspect('/reports/inventory-movements-summary?filter[warehouse_code]=WH01&filter[occurred_at_between]=today,today 23:59:59&per_page=200&sort=type')->screenshot();
//        $this->visitAndInspect('/reports/inventory-reservations?filter[warehouse_code]=WH01&sort=-created_at')->screenshot();
//        $this->visitAndInspect('/reports/restocking')->screenshot();
//        $this->visitAndInspect('/reports/stocktake-suggestions?filter[warehouse_code]=WH01&sort=-points')->screenshot();
//        $this->visitAndInspect('/reports/activity-log?sort=-id')->screenshot();
//        $this->visitAndInspect('/modules/scheduled-reports')->screenshot();
//
//        $this->say('Let me show you our products page');
//        $this->pause();
//        $this->say('You have everything under one finger');
//        $this->products($browser)
//            ->screenshot('products.png');
//
//        $this->pause();
//        $this->orders($browser)
//            ->screenshot('orders.png');
//
//        $this->pause();
//        $this->dataCollectorStockDelivery($browser)->screenshot('data-collector-stock-delivery.png');
//
//        $this->pause();
//        $this->stocktaking($browser)
//            ->screenshot('stocktaking.png');

        $this->pause();
        $this->picklist($browser)
            ->screenshot('picklist.png');

        $this->pause();
        $this->restocking($browser)->screenshot('restocking.png');

        $this->pause();
        $this->dashboard($browser)->screenshot('dashboard.png');

        $this->pause();
    }

    protected function setUp(): void
    {
        parent::setUp();

        Configuration::query()->update(['ecommerce_connected' => true]);

        InventoryTotalsServiceProvider::enableModule();
        InventoryMovementsServiceProvider::enableModule();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create(['name' => 'Dublin', 'code' => 'DUB']);

        $this->user = User::factory()->create([
            'warehouse_code' => $warehouse->code,
            'warehouse_id' => $warehouse->getKey(),
            'password' => bcrypt('password'),
        ]);

        $this->user->assignRole('admin');

        $this->product1 = Product::query()->where(['sku' => '111576'])->first() ?? Product::factory()->create(['sku' => '111576']);
        $this->product2 = Product::query()->where(['sku' => '222957'])->first() ?? Product::factory()->create(['sku' => '222957']);

        ProductPicture::factory()->create(['product_id' => $this->product1->getKey()]);

        $lang = ['en', 'fr'];
        foreach ($lang as $language) {
            ProductDescription::factory()->create(['product_id' => $this->product1->getKey(), 'language_code' => $language]);
        }

        $this->order = Order::factory()->create(['status_code' => 'paid']);

        /** @var OrderProduct $orderProduct1 */
        OrderProduct::factory()->create([
            'order_id' => $this->order->id,
            'sku_ordered' => $this->product1->sku,
            'name_ordered' => $this->product1->name,
            'product_id' => $this->product1->getKey(),
            'quantity_ordered' => 1,
        ]);

        /** @var OrderProduct $orderProduct2 */
        OrderProduct::factory()->create([
            'order_id' => $this->order->id,
            'sku_ordered' => $this->product2->sku,
            'name_ordered' => $this->product2->name,
            'product_id' => $this->product2->getKey(),
            'quantity_ordered' => 3,
        ]);
    }

    private function login(Browser $browser)
    {
        $browser->visit('/')
            ->pause($this->shortDelay)->assertPathIs('/login')
            ->pause($this->shortDelay)->type('email', $this->user->email)
            ->pause($this->shortDelay)->type('password', 'password')
            ->pause($this->shortDelay)->press('Login')
            ->pause($this->shortDelay)->assertPathBeginsWith('/dashboard')
            ->pause($this->longDelay);
    }

    private function dataCollectorStockDelivery(Browser $browser): Browser
    {
        $browser
            ->pause($this->shortDelay)->mouseover('#tools_link')
            ->pause($this->shortDelay)->click('#tools_link');

        $this->clickButton('#data_collector_link');

        $browser
//            ->pause($this->shortDelay)->mouseover('#data_collector_link')
//            ->pause($this->shortDelay)->clickLink('Data Collector')
            ->pause($this->shortDelay)->click('#new_data_collection')
            ->pause($this->shortDelay)->click('#create_blank_collection_button')
            ->pause($this->shortDelay)->typeSlowly('@collection_name_input', 'Stock delivery', 20)
            ->pause($this->shortDelay)->keys('@collection_name_input', '{enter}')
            ->pause($this->shortDelay)
            ->pause($this->shortDelay)->waitUntilMissing('#collection_name_input')
            ->pause($this->shortDelay)->waitFor('@data_collection_record')
            ->pause($this->shortDelay)
            ->pause($this->shortDelay)->mouseover('@data_collection_record')->pause($this->shortDelay)
            ->pause($this->shortDelay)->click('@data_collection_record')
            ->pause($this->shortDelay)->waitUntilMissing('@data_collection_record')
            ->pause($this->shortDelay)->waitFor('#data_collection_name')
            ->pause($this->longDelay);

        $this->order->orderProducts()
            ->where('quantity_to_ship', '>', 0)
            ->limit(4)
            ->get()
            ->each(function (OrderProduct $orderProduct) use ($browser) {
                $browser
                    ->pause($this->shortDelay)
                    ->pause($this->shortDelay)
                    ->keys('@barcode-input-field', $orderProduct->sku_ordered, '{ENTER}')
                    ->pause($this->shortDelay)
                    ->pause($this->shortDelay)
                    ->waitForText($orderProduct->sku_ordered)
                    ->pause($this->shortDelay)
                    ->pause($this->shortDelay)
                    ->waitFor('#data-collection-record-quantity-request-input')
                    ->pause($this->shortDelay)
                    ->typeSlowly('#data-collection-record-quantity-request-input', 12)
                    ->pause($this->shortDelay)
                    ->pause($this->shortDelay)
                    ->keys('#data-collection-record-quantity-request-input', '{ENTER}')
                    ->pause($this->longDelay);
            });

        $browser
            ->pause($this->shortDelay)->mouseover('#options-button')
            ->pause($this->shortDelay)->click('#options-button')
            ->pause($this->longDelay)
            ->pause($this->shortDelay)->mouseover('#transferInButton')
            ->pause($this->shortDelay)->click('#transferInButton')
            ->pause($this->longDelay);

        return $browser;
    }

    private function picklist(Browser $browser): Browser
    {
        $browser->pause($this->shortDelay)
            ->pause($this->shortDelay)->mouseover('#tools_link')
            ->pause($this->shortDelay)->click('#tools_link')
            ->pause($this->shortDelay)->mouseover('#picklist_link')
            ->pause($this->shortDelay)->click('#picklist_link')
            ->screenshot('picklist-link')
            ->pause($this->shortDelay)->waitForText('Status: paid')
            ->pause($this->shortDelay)->clickLink('Status: paid')
            ->pause($this->longDelay);

        $this->type('@barcode-input-field', $this->product1->sku, true);
        $browser->pause($this->longDelay);

        $this->type('@barcode-input-field', $this->product2->sku, true);
        $browser->pause($this->longDelay);

        return $browser;
    }

    private function dashboard(Browser $browser): Browser
    {
        $browser
            ->pause($this->shortDelay)->mouseover('#reports_link')
            ->pause($this->shortDelay)->clickLink('Reports')
            ->pause($this->shortDelay)->clickLink('Inventory Dashboard')
            ->pause($this->shortDelay)->mouseover('#reports_link')
            ->pause($this->shortDelay)->clickLink('Reports')
            ->pause($this->shortDelay)->clickLink('Orders Dashboard')
            ->pause($this->longDelay);

        return $browser;
    }

    private function stocktaking(Browser $browser): Browser
    {
        /** @var Product $product */
        $product = Product::first();

        $browser
            ->pause($this->shortDelay)->mouseover('#products_link')
            ->pause($this->shortDelay)->clickLink('Products')
            ->pause($this->shortDelay)
            ->pause($this->shortDelay)->mouseover('#stocktaking_link')
            ->pause($this->shortDelay)->clickLink('Stocktaking')
            ->pause($this->shortDelay)
            ->pause($this->shortDelay)->type('@barcode-input-field', $product->sku)
            ->pause($this->shortDelay)->screenshot('stocktaking')
            ->pause($this->shortDelay)->keys('@barcode-input-field', '{enter}')
            ->pause($this->shortDelay)
            ->pause($this->shortDelay)->waitForText($product->name)
            ->pause($this->shortDelay)
            ->pause($this->shortDelay)->waitFor('#quantity-request-input')
            ->pause($this->shortDelay)->typeSlowly('#quantity-request-input', 12)
            ->pause($this->shortDelay)->keys('#quantity-request-input', '{ENTER}')
            ->pause($this->longDelay);

        return $browser;
    }

    /**
     * @throws TimeoutException
     * @throws NoSuchElementException
     * @throws ElementClickInterceptedException
     */
    private function products(Browser $browser): Browser
    {
        $product = Product::with('productPicture', 'productDescriptions', 'inventory', 'prices')->first();
        $inventory = Inventory::where('product_id', $product->getKey())->first();
        InventoryMovement::factory()->create([
            'inventory_id' => $inventory->getKey(),
            'product_id' => $product->getKey(),
            'warehouse_code' => $inventory->warehouse_code,
            'warehouse_id' => $inventory->warehouse_id,
        ]);

        $this->clickButton('#products_link')
            ->clickButton('#inventory_link')
            ->typeSlowly('@barcode-input-field', $product->sku)
            ->clickEnter();

        $browser->pause($this->shortDelay)
            ->clickLink('fr')
            ->click('@show-inventory-movements-' . $inventory->id)
            ->within('#recent-inventory-movements-modal', function ($browser) {
                $browser->script([
                    "document.querySelector('button[dusk=\"cancel-button\"]').click();"
                ]);
            })
            ->pause($this->shortDelay)->click('@inventory-tab-' . $product->id)
            ->pause($this->shortDelay)->click('@order-tab-' . $product->id)
            ->pause($this->shortDelay)->click('@prices-tab-' . $product->id)
            ->pause($this->shortDelay)->click('@aliases-tab-' . $product->id)
            ->pause($this->shortDelay)->click('@labels-tab-' . $product->id)
            ->pause($this->shortDelay)->click('@activityLog-tab-' . $product->id);

        return $browser;
    }

    /**
     * @throws ElementClickInterceptedException
     * @throws NoSuchElementException
     */
    private function orders(Browser $browser): Browser
    {
        $this->clickButton('#orders_link')
            ->typeSlowly('@barcode-input-field', Order::first()->getAttribute('order_number'))
            ->clickEnter();

        return $browser;
    }

    private function restocking(Browser $browser): Browser
    {
        return $browser->mouseover('#tools_link')
            ->pause($this->shortDelay)->clickLink('Tools')
            ->pause($this->shortDelay)->mouseover('#restocking_link')
            ->pause($this->shortDelay)->clickLink('Restocking')
            ->pause($this->longDelay);
    }
}
