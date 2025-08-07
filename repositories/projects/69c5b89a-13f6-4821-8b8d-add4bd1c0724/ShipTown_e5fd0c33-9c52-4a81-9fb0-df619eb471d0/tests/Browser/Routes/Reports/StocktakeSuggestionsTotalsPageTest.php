<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;
use Throwable;

class StocktakeSuggestionsTotalsPageTest extends DuskTestCase
{
    private string $uri = '/reports/stocktake-suggestions-totals';

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
