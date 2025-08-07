<?php

namespace Tests\Browser\Routes\Products;

use Tests\DuskTestCase;
use Throwable;

class TransactionsPageTest extends DuskTestCase
{
    private string $uri = '/products/transactions';

    /**
     * @throws Throwable
     */
    public function testBasicTransaction()
    {
        $this->visit($this->uri);
        $this->startRecording();

        $this->browser()
            ->assertPathIs($this->uri);
    }
}
