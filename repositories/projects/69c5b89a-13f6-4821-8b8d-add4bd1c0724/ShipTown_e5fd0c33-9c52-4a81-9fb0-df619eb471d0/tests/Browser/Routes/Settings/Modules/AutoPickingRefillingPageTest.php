<?php

namespace Tests\Browser\Routes\Settings\Modules;

use Tests\DuskTestCase;

class AutoPickingRefillingPageTest extends DuskTestCase
{
    private string $uri = '/settings/modules/auto-picking-refilling';

    public function testPage()
    {
        $this->visit($this->uri);
    }
}
