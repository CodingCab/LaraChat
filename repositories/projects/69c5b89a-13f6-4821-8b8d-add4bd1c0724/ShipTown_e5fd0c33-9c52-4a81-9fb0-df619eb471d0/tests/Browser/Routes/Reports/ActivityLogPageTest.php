<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;

class ActivityLogPageTest extends DuskTestCase
{
    private string $uri = '/reports/activity-log';

    public function testPage(): void
    {
        $this->visit('dashboard');
        $this->startRecording('');

        $this->visit($this->uri);
        $this->typeAndEnter('test');

        $this->pause(2);
        $this->stopRecording();
    }
}
