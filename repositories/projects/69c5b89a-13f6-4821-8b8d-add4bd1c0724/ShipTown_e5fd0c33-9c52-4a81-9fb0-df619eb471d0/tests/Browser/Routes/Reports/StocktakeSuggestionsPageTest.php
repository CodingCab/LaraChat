<?php

namespace Tests\Browser\Routes\Reports;

use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class StocktakeSuggestionsPageTest extends DuskTestCase
{
    private string $uri = '/reports/stocktake-suggestions';

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
