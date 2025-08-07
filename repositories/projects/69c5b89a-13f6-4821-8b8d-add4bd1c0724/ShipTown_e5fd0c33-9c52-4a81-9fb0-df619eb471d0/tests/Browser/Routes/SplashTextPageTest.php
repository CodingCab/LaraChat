<?php

namespace Tests\Browser\Routes;

use Tests\DuskTestCase;
use Throwable;

class SplashTextPageTest extends DuskTestCase
{
    private string $uri = '/splash-text';

    /**
     * @throws Throwable
     */
    public function testPage()
    {
        $this->visit($this->uri);
        $this->browser()
            ->assertPathIs($this->uri);
    }
}
