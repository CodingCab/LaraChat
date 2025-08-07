<?php

namespace Tests\Browser\Components;

use App\Models\Product;
use Tests\DuskTestCase;
use Throwable;

class EditShelfLabelModalTest extends DuskTestCase
{
    /**
     * @throws Throwable
     */
    public function test_scanned_shelf_command_strips_prefix(): void
    {
        $product = Product::factory()->create();

        $this->visit('/products/inventory?search=' . $product->sku);

        $this->browser()
            ->click('th.cursor-pointer')
            ->waitFor('#edit-shelf-label-modal')
            ->keys('#shelf_label_input', 'shelf:C34')
            ->pause(300)
            ->assertInputValue('#shelf_label_input', 'C34');
    }
}
