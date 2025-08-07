<?php

namespace Tests\Browser\Routes\Products;

use Tests\DuskTestCase;
use Throwable;

class OfflineInventoryPageTest extends DuskTestCase
{
    private string $uri = '/products/offline-inventory';

    /**
     * @throws Throwable
     */
    public function testHowToPutStockAway()
    {
        $this->visit($this->uri);
        $this->startRecording();
        $this->browser()
            ->assertPathIs($this->uri);
    }
}
