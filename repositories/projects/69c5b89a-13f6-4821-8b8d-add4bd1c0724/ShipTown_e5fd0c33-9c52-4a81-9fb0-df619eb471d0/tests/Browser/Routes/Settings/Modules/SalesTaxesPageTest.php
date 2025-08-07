<?php

namespace Tests\Browser\Routes\Settings\Modules;

use Tests\DuskTestCase;
use Throwable;

class SalesTaxesPageTest extends DuskTestCase
{
    private string $uri = '/settings/modules/sales-taxes';

    /**
     * @throws Throwable
     */
    public function testPage()
    {
        $this->basicAdminAccessTest($this->uri, true);
    }
}
