<?php

namespace Tests\Browser\Routes\Products;

use Tests\DuskTestCase;
use Throwable;

class TransfersOutPageTest extends DuskTestCase
{
    private string $uri = '/products/transfers-out';

    /**
     * @throws Throwable
     */
    public function testHowToTransferStockOut()
    {
        $this->visit($this->uri);
        $this->startRecording();

        $this->browser()
            ->assertPathIs($this->uri);
    }
}
