<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;
use Throwable;

class ShipmentsPageTest extends DuskTestCase
{
    private string $uri = '/reports/shipments';

    /**
     * @throws Throwable
     */
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
