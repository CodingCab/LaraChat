<?php

namespace Tests\Browser\Routes\Password;

use Tests\DuskTestCase;

class ResetPageTest extends DuskTestCase
{
    private string $uri = 'password/reset';

    public function testIncomplete(): void
    {
        $this->browser()->visit($this->uri);
        $this->browser()->assertPresent('#email');
        $this->browser()->assertPresent('#submit-button');
    }
}
