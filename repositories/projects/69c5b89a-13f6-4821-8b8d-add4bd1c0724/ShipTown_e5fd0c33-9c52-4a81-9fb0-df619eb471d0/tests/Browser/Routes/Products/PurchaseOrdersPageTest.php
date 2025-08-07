<?php

namespace Tests\Browser\Routes\Products;

use Tests\DuskTestCase;
use Throwable;

class PurchaseOrdersPageTest extends DuskTestCase
{
    private string $uri = '/products/purchase-orders';

    /**
     * @throws Throwable
     */
    public function testCreatingPurchaseOrder()
    {
        $this->visit($this->uri);
        $this->startRecording();

        $this->browser()
            ->assertPathIs($this->uri);
    }
}
