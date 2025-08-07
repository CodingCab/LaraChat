<?php

namespace Tests\Browser\Routes\Settings;

use Tests\DuskTestCase;
use Throwable;

class AutomationsReportPageTest extends DuskTestCase
{
    private string $uri = '/settings/automations';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        $this->basicUserAccessTest($this->uri, true);
    }
}
