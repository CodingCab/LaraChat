<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;
use Throwable;

class CopyAsTableButtonTest extends DuskTestCase
{
    private string $uri = '/reports/inventory-movements';

    /**
     * @throws Throwable
     */
    public function test_button_visible(): void
    {
        $this->visit('inventory-dashboard');
        $this->clickButton('#reports_link');
        $this->clickButton('#inventory_movements_report');
        $this->clickButton('#options-button');

        $this->browser()->assertSee('Copy as table');
    }
}
