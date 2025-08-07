<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;

class OrderFulfillmentTimePageTest extends DuskTestCase
{
    private string $uri = '/reports/order-fulfillment-time';

    public function testPage(): void
    {
        $this->visit('dashboard');
        $this->startRecording('');

        $this->visit($this->uri);
        $this->typeAndEnter('test');

        $this->stopRecording();
    }
}
