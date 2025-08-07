<?php

namespace Tests\Browser\Routes;

use Tests\DuskTestCase;
use Throwable;

class FlowchartSamplePageTest extends DuskTestCase
{
    private string $uri = '/flowchart-sample';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        $this->basicUserAccessTest($this->uri, true);
    }
}
