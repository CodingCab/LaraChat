<?php

namespace Tests\Browser\Routes\Products;

use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\Warehouse;
use App\User;
use Facebook\WebDriver\WebDriverKeys;
use Tests\DuskTestCase;
use Throwable;

class StocktakingPageTest extends DuskTestCase
{
    private string $uri = '/products/stocktaking';

    /**
     * @throws Throwable
     */
    public function testIfPageDisplaysCorrectly(): void
    {
        $browser= $this->browser();

        $browser->loginAs($this->user)
            ->visit($this->uri)
            ->pause($this->shortDelay);

        $browser->assertSee('PRODUCTS > STOCKTAKING')
            ->assertSee('SEE MORE')
            ->assertSee('REPORTS > STOCKTAKE SUGGESTIONS')
            ->assertSourceMissing('snotify-error')
            ->assertFocused('@barcode-input-field');
    }

    /**
     * @throws Throwable
     */
    public function testHowToStocktakeProducts(): void
    {
        $browser = $this->browser();

        $browser->loginAs($this->user);
        $browser->visit($this->uri);
        $browser->pause($this->shortDelay);
        $browser->assertSourceMissing('snotify-error');

        $this->startRecording();

        Product::factory(3)->create()
            ->each(function (Product $product) use ($browser) {
                $browser->pause($this->shortDelay);
                $browser->assertFocused('@barcode-input-field');
                $browser->pause($this->shortDelay);

                $this->sendKeysTo($browser, $product->sku);
                $browser->pause(20);
                $this->sendKeysTo($browser, WebDriverKeys::ENTER);
                $browser->waitFor('#quantity-request-input');
                $browser->pause($this->shortDelay);
                $browser->assertFocused('@quantity-request-input');

                $browser->assertSee($product->sku);
                $browser->assertSee($product->name);

                $randomQuantity = rand(0, 10000);
                $this->sendKeysTo($browser, $randomQuantity);
                $browser->pause(20);
                $this->sendKeysTo($browser, WebDriverKeys::ENTER);
                $browser->pause($this->shortDelay);

                $browser->waitForText('Stocktake updated');
                $browser->waitForText($randomQuantity.' x '.$product->sku);
                $browser->assertMissing('#quantity-request-input');
                $browser->assertFocused('@barcode-input-field');

            });
    }

    /**
     * @throws Throwable
     */
    public function testIfNegativeQuantityNotAllowed(): void
    {
        $browser = $this->browser();

        /** @var Product $product */
        $product = Product::factory()->create();

        $browser->loginAs($this->user)
            ->visit($this->uri)
            ->pause($this->shortDelay)
            ->assertSourceMissing('snotify-error');

        $this->type('@barcode-input-field', $product->sku, true);

        $browser->waitFor('#quantity-request-input')
            ->pause($this->shortDelay)
            ->assertSee($product->sku)
            ->assertSee($product->name);

        $this->typeAndEnter(-1, false);

        $browser->waitForText('Minus quantity not allowed');
        $browser->assertVisible('#quantity-request-input');
        $browser->assertFocused('#quantity-request-input');

        $this->sendKeysTo($browser, WebDriverKeys::ESCAPE);

        $browser->pause($this->shortDelay);
        $browser->assertFocused('@barcode-input-field');
    }

    /**
     * @throws Throwable
     */
    public function testIfAliasScans(): void
    {
        $browser = $this->browser();

        $browser->loginAs($this->user)
            ->visit($this->uri)
            ->pause($this->shortDelay)
            ->assertSourceMissing('snotify-error')
            ->assertFocused('@barcode-input-field');

        $this->startRecording();

        /** @var ProductAlias $alias */
        $alias = ProductAlias::query()->inRandomOrder()->first() ?? ProductAlias::factory()->create();

        $browser->assertFocused('@barcode-input-field');

        $this->sendKeysTo($browser, $alias->product->sku);
        $this->sendKeysTo($browser, WebDriverKeys::ENTER);

        $browser->waitFor('#quantity-request-input')
            ->pause($this->shortDelay)
            ->assertFocused('#quantity-request-input')
            ->assertSee($alias->product->sku)
            ->assertSee($alias->product->name);

        $this->sendKeysTo($browser, rand(0, 10000));
        $this->sendKeysTo($browser, WebDriverKeys::ENTER);

        $browser->waitForText('Stocktake updated')
            ->assertMissing('#quantity-request-input')
            ->pause($this->shortDelay)
            ->assertFocused('@barcode-input-field');
    }

    /**
     * @throws Throwable
     */
    public function testIfNotifiesWhenProductNotFound(): void
    {
        $this->browser()
            ->loginAs($this->user)
            ->visit($this->uri)
            ->pause(1500)->assertSourceMissing('snotify-error')->assertFocused('@barcode-input-field')
            ->typeAndEnter('not-existing-sku')->waitForText('Product not found')
            ->assertSourceHas('snotify-error');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Warehouse::factory()->create();
        $this->user = User::factory()->create();
    }
}
