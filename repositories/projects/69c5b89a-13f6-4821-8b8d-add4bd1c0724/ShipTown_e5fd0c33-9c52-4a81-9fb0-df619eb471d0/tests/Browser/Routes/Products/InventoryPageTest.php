<?php

namespace Tests\Browser\Routes\Products;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\ProductDescription;
use App\Models\ProductPicture;
use App\User;
use Facebook\WebDriver\Exception\TimeoutException;
use Spatie\Activitylog\Models\Activity;
use Tests\DuskTestCase;
use Throwable;

class InventoryPageTest extends DuskTestCase
{
    private string $uri = '/products/inventory';

    public function testCreateNewProduct()
    {
        $this->visit($this->uri);

        $this->startRecording('How to create a new product ?');

        $this->pause()
            ->say('Hi Everyone!')
            ->say('In today\'s lesson I will show you how to create a new product.');

        $this->say('First, click on Products')
            ->clickButton('#products_link');

        $this->say('and then Inventory')
            ->clickButton('#inventory_link');

        $this->say('In the top right corner click the PLUS button')
            ->clickButton('#plus_button');

        $this->say('Fill in the basic information about the product')
            ->clickButton('#newProductSku')
            ->type('#newProductSku', '123456');

        $this->say(', such as SKU, name and price of the product.')
            ->clickButton('#newProductName')
            ->typeSlowly('#newProductName', 'Blue Watch');

        $this->clickButton('#newProductPrice')
            ->typeSlowly('#newProductPrice', '79');

        $this->say('Click "CREATE" button to save the product and see its preview.')
            ->clickButton('#ok_button');

        $this->say('If you want to create or update many products at once, use the import from CSV file feature, but I will show you that in the next lesson.')
            ->clickButton('#plus_button');

        $this->pause(2);
    }

    /**
     * @throws Throwable
     */
    public function testIfDisplaysProducts(): void
    {
        $browser = $this->browser()
            ->loginAs($this->user)
            ->visit($this->uri);

        $this->startRecording('How to navigate through Inventory Page?');

        $browser->displayText('Products > Inventory');
        $browser->pause(1000);

        $this->say('Here we gonna show you how to navigate trough out Inventory Page');
        $browser->pause(2000)
            ->visitAndWaitForText($this->uri, 'PRODUCTS');

        $this->say('Scan your product or type in product SKU or name to find it ');
        $browser->pause(2000)
            ->typeAndEnter($this->product->sku)
            ->waitForText($this->product->name)
            ->assertSee($this->product->sku)
            ->assertVisible('img[src="' . $this->product->productPicture->url . '"]');

        $browser->clickLink('fr')
            ->assertSee($this->frDescription->description);
        $browser->pause(2000);
        $browser->click('@show-inventory-movements-' . $this->inventory->id);
        $browser->assertVisible('#recent-inventory-movements-modal');
        $browser->within('#recent-inventory-movements-modal', function ($browser) {
            $browser->waitFor('@product-info-card-' . $this->product->id)
                ->assertSee($this->product->name)
                ->assertSee($this->product->sku)
                ->assertVisible('img[src="' . $this->product->productPicture->url . '"]');

//            $this->clickButton('btn-close-modal-recent-inventory-movements-modal');

            $browser->script([
                "document.querySelector('button[id=\"btn-close-modal-recent-inventory-movements-modal\"]').click();"
            ]);
        })
        ->pause($this->shortDelay)->assertMissing('#recent-inventory-movements-modal')

            // Test Inventory Tab
            ->click('@inventory-tab-' . $this->product->id)
            ->pause(2000)
            ->assertVisible('@inventory-detail-' . $this->product->id)
            ->within('@inventory-detail-' . $this->product->id, function ($browser) {
                foreach ($this->product->inventory as $inventory) {
                    $browser->assertVisible('@inventory-' . $inventory->id)
                        ->within('@inventory-' . $inventory->id, function ($browser) use ($inventory) {
                            $browser->assertSee($inventory->warehouse_code);
                        });
                }
            })

            // Test Order Tab
            ->click('@order-tab-' . $this->product->id)
            ->pause(2000)
            ->assertVisible('@order-detail-' . $this->product->id)
            ->within('@order-detail-' . $this->product->id, function ($browser) {
                OrderProduct::with('order')
                    ->where('product_id', $this->product->getKey())
                    ->whereHas('order', function ($query) {
                        $query->where('is_active', true);
                    })
                    ->get()
                    ->each(function ($orderProduct) use ($browser) {
                        $browser->assertVisible('@order-product-' . $orderProduct->id)
                            ->within('@order-product-' . $orderProduct->id, function ($browser) use ($orderProduct) {
                                $browser->assertSee($orderProduct->order->order_number);
                            });
                    });
            })

            // Test Pricing Tab
            ->click('@prices-tab-' . $this->product->id)
            ->pause(2000)
            ->assertVisible('@prices-detail-' . $this->product->id)
            ->within('@prices-detail-' . $this->product->id, function ($browser) {
                foreach ($this->product->prices as $price) {
                    $browser->assertVisible('@price-' . $price->id)
                        ->within('@price-' . $price->id, function ($browser) use ($price) {
                            $browser->assertSee($price->warehouse_code)
                                ->assertSee($price->price)
                                ->assertSee($price->sale_price);
                        });
                }
            })

            // Test Aliases Tab
            ->click('@aliases-tab-' . $this->product->id)
            ->pause(2000)
            ->assertVisible('@aliases-detail-' . $this->product->id)
            ->within('@aliases-detail-' . $this->product->id, function ($browser) {
                $this->product->aliases->each(function ($alias) use ($browser) {
                    $browser->assertVisible('@alias-' . $alias->id)
                        ->within('@alias-' . $alias->id, function ($browser) use ($alias) {
                            $browser->assertSee($alias->alias)
                                ->assertValue("@alias-quantity-input-{$alias->id}", $alias->quantity);
                        });
                });

                $browser->assertVisible('#newProductAliasInput-'.$this->product->id)
                    ->keys('#newProductAliasInput-'.$this->product->id, 'NewAlias')
                    ->keys('#newProductAliasInput-'.$this->product->id, '{enter}')
                    ->pause(2000)
                    ->screenshot('new-alias');

                $newAlias = ProductAlias::where('alias', 'NewAlias')
                    ->where('product_id', $this->product->id)
                    ->first();

                $browser->assertVisible('@alias-' . $newAlias->id)
                    ->within('@alias-' . $newAlias->id, function ($browser) use ($newAlias) {
                        $browser->assertSee($newAlias->alias)
                            ->assertValue("@alias-quantity-input-{$newAlias->id}", $newAlias->quantity)
                            ->keys("@alias-quantity-input-{$newAlias->id}", '12')
                            ->keys("@alias-quantity-input-{$newAlias->id}", '{enter}');
                    });
            })

            // Test Labels Tab
            ->click('@labels-tab-' . $this->product->id)
            ->pause(2000)
            ->assertVisible('@labels-detail-' . $this->product->id)
            ->within('@labels-detail-' . $this->product->id, function ($browser) {
                $browser->waitFor('.vue-pdf-embed')
                    ->assertVisible('.vue-pdf-embed')
                    ->assertVisible('.vue-pdf-embed__page')
                    ->assertVisible('canvas');
            })

            // Test Activity Tab
            ->click('@activityLog-tab-' . $this->product->id)
            ->assertVisible('@activityLog-detail-' . $this->product->id)
            ->pause(2000)
            ->within('@activityLog-detail-' . $this->product->id, function ($browser) {
                Activity::where('subject_id', $this->product->id)
                    ->where('subject_type', 'App\Models\Product')
                    ->get()
                    ->each(function (Activity $activity) use ($browser) {
                        $browser->within('@activity-' . $activity->id, function ($browser) use ($activity) {
                            $browser->assertSee($activity->description)
                                ->assertSee($activity->causer ? $activity->causer->name : 'AutoPilot');
                        });
                    });
            })

            // Test Weight Tab
            ->click('@weight-tab-' . $this->product->id)
            ->assertVisible('@weight-detail-' . $this->product->id)
            ->within('@weight-detail-' . $this->product->id, function ($browser) {
                $browser->assertValue('@product-weight-input', $this->product->weight)
                    ->assertValue('@product-length-input', $this->product->length)
                    ->assertValue('@product-width-input', $this->product->width)
                    ->assertValue('@product-height-input', $this->product->height)

                    ->keys('@product-weight-input', '2')->keys('@product-weight-input', '{enter}')
                    ->keys('@product-length-input', '12')->keys('@product-length-input', '{enter}')
                    ->keys('@product-width-input', '12')->keys('@product-width-input', '{enter}')
                    ->keys('@product-height-input', '12')->keys('@product-height-input', '{enter}')


                    ->assertValue('@product-volumetric-weight-input', 12 * 12 * 12);
            });
    }

    /**
     * @throws Throwable
     * @throws TimeoutException
     */
    public function testNoProducts(): void
    {
        Product::query()->forceDelete();

        $this->visit($this->uri);
        $this->browser()
            ->pause($this->shortDelay)
            ->assertSourceMissing('Server Error')
            ->assertSourceMissing('snotify-error');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create();

        ProductPicture::factory()->create(['product_id' => $this->product->getKey()]);

        $lang = ['en', 'fr'];
        foreach ($lang as $language) {
            ProductDescription::factory()->create(['product_id' => $this->product->getKey(), 'language_code' => $language]);
        }

        $this->frDescription = $this->product->productDescriptions->where('language_code', 'fr')->first();

        $this->inventory = Inventory::where('product_id', $this->product->getKey())->first();
        InventoryMovement::factory()->create([
            'inventory_id' => $this->inventory->getKey(),
            'product_id' => $this->product->getKey(),
            'warehouse_code' => $this->inventory->warehouse_code,
            'warehouse_id' => $this->inventory->warehouse_id,
        ]);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }
}
