<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;
use Throwable;

class InventoryReservationsPageTest extends DuskTestCase
{
    private string $uri = '/reports/inventory-reservations';

    /**
     * @throws Throwable
     */
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
