<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;

class DataCollectionsPageTest extends DuskTestCase
{
    private string $uri = '/reports/data-collections';

    public function testPage()
    {
        $this->visit('dashboard');
//        $this->startRecording('');

        $this->visit($this->uri);

        // $this->say('Hello World');
        // $this->clickButton('#options-button');
        // $this->pause(2);
        // $this->clickEscape();

//        $this->pause(2);
//        $this->stopRecording();
    }
}
