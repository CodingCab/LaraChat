<?php

namespace Tests\Browser\Routes;

use Tests\DuskTestCase;
use Throwable;

class LoginPageTest extends DuskTestCase
{
    private string $uri = '/login';

    /**
     * @throws Throwable
     */
    public function testBasics(): void
    {
        $this->browser()->visit($this->uri);
        $this->browser()->assertPresent('#email');
        $this->browser()->assertPresent('#password');
        $this->browser()->click('#login-button');
    }
}
