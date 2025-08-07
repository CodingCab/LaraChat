<?php

namespace Tests\Browser\Routes\Settings\Modules\Couriers;

use Tests\DuskTestCase;
use Throwable;

class DpdPolandPageTest extends DuskTestCase
{
    private string $uri = '/settings/modules/couriers/dpd-poland';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        $this->basicUserAccessTest($this->uri, true);
    }
}
