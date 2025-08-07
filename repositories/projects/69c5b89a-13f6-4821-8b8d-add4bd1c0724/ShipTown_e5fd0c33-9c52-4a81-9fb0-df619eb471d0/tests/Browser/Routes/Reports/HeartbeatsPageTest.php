<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;

class HeartbeatsPageTest extends DuskTestCase
{
    private string $uri = '/reports/heartbeats';

    public function testPage(): void
    {
        $this->visit('dashboard');
        $this->visit($this->uri);
    }
}
