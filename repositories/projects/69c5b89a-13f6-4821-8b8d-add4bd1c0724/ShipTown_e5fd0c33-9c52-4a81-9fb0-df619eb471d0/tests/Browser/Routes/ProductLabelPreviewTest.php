<?php

namespace Tests\Browser\Components;

use App\Models\Product;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class ProductLabelPreviewTest extends DuskTestCase
{
    /**
     * @throws Throwable
     */
    public function test_preview_limited_message_is_displayed(): void
    {
        $product = Product::factory()->create();

        $this->visit('/products/inventory?search=' . $product->sku);

        $this->browser()
            ->click('th.cursor-pointer')
            ->click('@labels-tab-' . $product->id)
            ->waitFor('@labels-detail-' . $product->id)
            ->type('@product-label-count-input', '30')
            ->pause(500)
            ->assertSee('Preview limited to 25 labels');
    }
}

