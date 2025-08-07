<?php

namespace App\Tests;

use Throwable;

class InventoryDashboardPageTestNew extends LoginPageTestNew
{
    private string $uri = '/inventory-dashboard';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        $browser = $this->browser();

        $browser->visit($this->uri);
        $browser->assertPathIs($this->uri);
        $browser->assertSourceMissing('Server Error');
    }
}
