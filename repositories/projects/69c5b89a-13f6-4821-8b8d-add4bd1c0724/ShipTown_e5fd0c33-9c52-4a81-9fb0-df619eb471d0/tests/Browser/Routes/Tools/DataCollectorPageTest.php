<?php

namespace Tests\Browser\Routes\Tools;

use Tests\DuskTestCase;
use Throwable;

class DataCollectorPageTest extends DuskTestCase
{
    private string $uri = '/tools/data-collector';

    /**
     * @throws Throwable
     */
    public function testPage()
    {
        $this->visit($this->uri);
        $this->startRecording();

        $this->browser()
            ->assertPathIs($this->uri);
    }
}
