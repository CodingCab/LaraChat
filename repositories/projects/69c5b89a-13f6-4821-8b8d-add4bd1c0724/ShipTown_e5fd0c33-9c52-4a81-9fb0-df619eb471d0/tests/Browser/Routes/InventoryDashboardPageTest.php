<?php

namespace Tests\Browser\Routes;

use Tests\DuskTestCase;
use Throwable;

class InventoryDashboardPageTest extends DuskTestCase
{
    private string $uri = '/inventory-dashboard';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        $browser = $this->browser();

        $this->visit($this->uri);
        $browser->assertPathIs($this->uri);
        $browser->assertSourceMissing('Server Error');
    }
}
