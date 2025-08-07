<?php

namespace Tests\Browser\Routes\Tools;

use Tests\DuskTestCase;

class PicklistFocusTest extends DuskTestCase
{
    private string $uri = '/tools/picklist';

    public function testBarcodeInputFocusedAfterLoadingFilter(): void
    {
        $this->visit('dashboard');

        $this->clickButton('#tools_link');
        $this->clickButton('#picklist_link');

        $this->browser()
            ->waitForText('Status: paid')
            ->clickLink('Status: paid')
            ->waitFor('@barcode-input-field')
            ->assertFocused('@barcode-input-field');
    }
}
