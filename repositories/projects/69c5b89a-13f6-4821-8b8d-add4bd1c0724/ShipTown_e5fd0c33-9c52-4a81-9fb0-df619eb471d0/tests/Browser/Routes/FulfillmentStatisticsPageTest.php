<?php

namespace Tests\Browser\Routes;

use Tests\DuskTestCase;
use Throwable;

class FulfillmentStatisticsPageTest extends DuskTestCase
{
    private string $uri = '/fulfillment-statistics';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        $this->visit($this->uri);
    }

    public function testDateSelectorIsVisible(): void
    {
        $this->visit($this->uri);

        $this->browser()
            ->assertVisible('#dropdownDateRange')
            ->assertDontSee('Invalid date');
    }

    public function testCustomDateFilterUpdatesUrl(): void
    {
        $this->visit($this->uri);

        $this->clickButton('#dropdownDateRange');
        $this->clickButton('@custom-date-link'); // Try for up to 5 seconds

        $this->type('#starting_date', '2024-01-01 00:00');
        $this->type('#ending_date', '2024-01-02 00:00');
        $this->clickButton('#modal-date-between-filter-apply');
        $this->clickEscape();
    }
}
