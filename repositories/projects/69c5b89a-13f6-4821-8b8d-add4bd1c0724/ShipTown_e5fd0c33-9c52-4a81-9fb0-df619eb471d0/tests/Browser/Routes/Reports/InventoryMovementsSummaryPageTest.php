<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;

class InventoryMovementsSummaryPageTest extends DuskTestCase
{
    private string $uri = '/reports/inventory-movements-summary';

    public function testPage(): void
    {
        $this->visit('dashboard');
        $this->startRecording('');

        $this->visit($this->uri);
        $this->typeAndEnter('test');

        $this->pause(2);
        $this->stopRecording();
    }
}
