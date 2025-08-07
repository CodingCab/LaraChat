<?php

namespace Tests\Browser\Routes;

use Tests\DuskTestCase;
use Throwable;

class PageTest extends DuskTestCase
{
    private string $uri = '/';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        $this->browser()->logout()->visit($this->uri);

        $this->browser()
            ->assertPathIs($this->uri);
    }
}
