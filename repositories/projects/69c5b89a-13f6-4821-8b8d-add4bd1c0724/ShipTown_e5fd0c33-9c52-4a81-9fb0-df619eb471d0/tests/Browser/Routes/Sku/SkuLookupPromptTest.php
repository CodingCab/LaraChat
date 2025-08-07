<?php
namespace Tests\Browser\Components;

use Database\Factories\Modules\Api2cart\src\Models\Api2cartConnectionFactory;
use Tests\DuskTestCase;
use Throwable;

class SkuLookupPromptTest extends DuskTestCase
{
    /**
     * Ensure SKU Lookup prompt shows correct title.
     *
     * @throws Throwable
     */
    public function test_prompt_title_is_sku_lookup(): void
    {
        // create a connection so the page has at least one row
        Api2cartConnectionFactory::new()->create(['url' => 'https://example.com']);

        $this->visit('/settings/api2cart', $this->testAdmin);

        $this->browser()
            ->clickLink('SKU Lookup')
            ->waitFor('.snotifyToast__title')
            ->assertSeeIn('.snotifyToast__title', 'SKU Lookup');
    }
}
