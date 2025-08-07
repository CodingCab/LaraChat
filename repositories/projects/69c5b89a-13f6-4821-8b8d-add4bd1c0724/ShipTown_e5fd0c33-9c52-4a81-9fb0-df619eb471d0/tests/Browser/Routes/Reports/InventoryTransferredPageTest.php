<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;

class InventoryTransferredPageTest extends DuskTestCase
{
    private string $uri = '/reports/inventory-transferred';

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
