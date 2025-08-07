<?php

namespace Tests\Browser\Routes\Reports;

use Tests\DuskTestCase;

class DataCollectionsRecordsPageTest extends DuskTestCase
{
    private string $uri = '/reports/data-collections-records';

    public function testPage()
    {
        $this->visit('/');
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
