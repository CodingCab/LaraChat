<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;

class InventoryStocktakesPageTest extends DuskTestCase
{
    private string $uri = '/reports/inventory-stocktakes';

    public function testPage()
    {
        $this->visit('dashboard');
        $this->startRecording('');

        $this->visit($this->uri);
        $this->typeAndEnter('test');

        $this->pause(2);
        $this->stopRecording();
    }
}
