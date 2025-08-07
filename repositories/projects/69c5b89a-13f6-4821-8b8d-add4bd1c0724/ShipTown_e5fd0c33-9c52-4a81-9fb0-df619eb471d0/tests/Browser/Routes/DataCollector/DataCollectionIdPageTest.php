<?php

namespace Tests\Browser\Routes\DataCollector;

use App\Models\DataCollection;
use App\Models\Product;
use App\Models\ProductAlias;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DataCollectionIdPageTest extends DuskTestCase
{
    private string $uri = '/data-collector/';

    public function testIfDisplaysDataCollector()
    {
        $dataCollection = DataCollection::factory()->create();
        $this->visit($this->uri . $dataCollection->id);
        $this->browser()
            ->waitFor('#data_collection_name')
            ->assertSee($dataCollection->name);
    }

    public function testScanAliasSku(): void
    {
        $product = Product::factory()->create();
        $dataCollection = DataCollection::factory()->create();
        $productAlias = ProductAlias::factory()->create([
            'product_id' => $product->getKey(),
        ]);
        $this->visit($this->uri . $dataCollection->id);

        $this->startRecording('How to scan product alias SKU?');

        $this->browser()
            ->waitFor('@barcode-input-field', 2)
            ->pause(1000)
            ->type('@barcode-input-field', $productAlias->alias)
            ->keys('@barcode-input-field', '{enter}')
            ->waitFor('#data-collector-quantity-request-modal')
            ->within('#data-collector-quantity-request-modal', function (Browser $browser) use($productAlias) {
                $browser->pause($this->shortDelay)
                    ->click('#keypad7');
                $this->clickButton('#keypadOk');
            })
            ->waitUntilMissing('#data-collector-quantity-request-modal')
            ->pause(2000);
    }

}
