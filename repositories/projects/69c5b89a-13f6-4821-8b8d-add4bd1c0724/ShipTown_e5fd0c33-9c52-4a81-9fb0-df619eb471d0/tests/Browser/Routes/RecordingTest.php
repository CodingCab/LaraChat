<?php

namespace Tests\Browser\Routes;

use Tests\DuskTestCase;

class RecordingTest extends DuskTestCase
{
    public function testScreenRecording(): void
    {
            $browser = $this->browser();
            $browser->visit('/');

            $this->startRecording('Screen Recording Test');
            $browser->displayText(now()->toDateTimeString());

            $this->say('1 2 3 Testing');
            $this->pause();

            $this->stopRecording();
    }
}
