<?php

namespace Tests\Browser\Routes;

use Tests\DuskTestCase;

class ScreenshotsPageTest extends DuskTestCase
{
    private string $uri = '/screenshots';

    public function testScreenshot()
    {
        $this->visit($this->uri);

        $this->screenshot();
    }
}
