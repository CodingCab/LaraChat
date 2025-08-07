<?php

namespace Tests\Browser\Routes\Products;

use Tests\DuskTestCase;
use Throwable;

class TransfersInPageTest extends DuskTestCase
{
    private string $uri = '/products/transfers-in';

    /**
     * @throws Throwable
     */
    public function testHowToReceiveTransferIn()
    {
        $this->visit($this->uri);
        $this->startRecording();

        $this->browser()
            ->assertPathIs($this->uri);
    }
}
