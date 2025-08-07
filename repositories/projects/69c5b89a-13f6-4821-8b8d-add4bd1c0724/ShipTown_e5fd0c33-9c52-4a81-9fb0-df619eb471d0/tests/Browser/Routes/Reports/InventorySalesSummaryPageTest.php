<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;

class InventorySalesSummaryPageTest extends DuskTestCase
{
    private string $uri = '/reports/inventory-sales-summary';

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
