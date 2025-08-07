<?php

namespace Tests\Browser\Routes\Settings\Modules;

use Tests\DuskTestCase;
use Throwable;

class FakturowoPageTest extends DuskTestCase
{
    private string $uri = '/settings/modules/fakturowo';

    /**
     * @throws Throwable
     */
    public function testPage()
    {
        $this->basicUserAccessTest($this->uri, true);
    }
}
