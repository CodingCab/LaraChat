<?php
namespace Tests\Browser\Components;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\Warehouse;
use Tests\DuskTestCase;
use Throwable;

class ShelfLocationCommandModalTest extends DuskTestCase
{
    /**
     * @throws Throwable
     */
    public function test_ok_button_updates_shelf(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'product_sku' => $product->sku,
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'shelve_location' => 'A1',
        ]);

        $this->visit('/products/inventory?search=' . $product->sku, $this->testAdmin);

        $this->browser()
            ->script("document.getElementById('app').__vue__.$modal.showShelfLocationCommandModal({name:'shelf', value:'B2'}, function(){})");

        $this->browser()
            ->waitFor('#set-shelf-location-command-modal')
            ->type('#set-shelf-location-command-modal-input', $product->sku)
            ->click('#shelf_modal_ok_button')
            ->waitUntilMissing('#set-shelf-location-command-modal');

        $inventory->refresh();
        $this->assertEquals('B2', $inventory->shelve_location);
    }
}
