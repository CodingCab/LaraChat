<?php

namespace Tests\Browser\Routes\Settings\Modules;

use Tests\DuskTestCase;
use Throwable;

class DataCollectorDiscountsPageTest extends DuskTestCase
{
    private string $uri = '/settings/modules/data-collector-discounts';

    /**
     * @throws Throwable
     */
    public function testPage()
    {
        $this->basicUserAccessTest($this->uri, true);
    }
}
