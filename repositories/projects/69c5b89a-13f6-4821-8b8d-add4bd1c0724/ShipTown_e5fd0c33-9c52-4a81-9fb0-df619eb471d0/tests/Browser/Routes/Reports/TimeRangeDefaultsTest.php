<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;
use Throwable;

class TimeRangeDefaultsTest extends DuskTestCase
{
    /**
     * @throws Throwable
     */
    public function testDefaultTimeIsPreFilled(): void
    {
        $this->visit('inventory-dashboard');

        $this->clickButton('#reports_link');
        $this->clickButton('#inventory_movements_report');
        $this->clickButton('#columns-button');
        $this->clickButton('#show-hide-columns-occurred_at');
        $this->clickButton('#show-hide-columns-filter-by-value-occurred_at');
        $this->clickButton('#modal-date-between-filter-select-date-range');

        $this->browser()
            ->assertInputValue('#starting_date', now()->startOfDay()->format('Y-m-d\TH:i'))
            ->assertInputValue('#ending_date', now()->endOfDay()->format('Y-m-d\TH:i'));
    }
}
