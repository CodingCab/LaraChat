<?php

namespace App\Tests;

use App\Abstracts\BrowserTestCase;
use Throwable;

class LoginPageTestNew extends BrowserTestCase
{
    private string $uri = '/login';

    /**
     * @throws Throwable
     */
    public function testBasics(): void
    {
        $this->browser()->logout();
        $this->browser()->visit($this->uri);
        $this->browser()->assertPresent('#email');
        $this->browser()->assertPresent('#password');
        $this->browser()->click('#login-button');
    }
}
