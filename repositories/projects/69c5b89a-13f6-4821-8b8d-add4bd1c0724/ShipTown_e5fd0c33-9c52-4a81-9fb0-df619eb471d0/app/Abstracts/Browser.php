<?php

namespace App\Abstracts;

use Facebook\WebDriver\WebDriverKeys;
use Laravel\Dusk\Browser as DuskBrowser;

class Browser extends DuskBrowser
{
    public function displayText(string $text, int $seconds = 2): Browser
    {
        return $this->visit('splash-text?text='. $text)
            ->pause($seconds * 1000);
    }

    public function typeAndEnter(mixed $text, int $milliseconds_pause = 300): Browser
    {
        $this->driver->getKeyboard()->sendKeys($text);
        $this->pause(20);
        $this->driver->getKeyboard()->sendKeys(WebDriverKeys::ENTER);
        $this->pause($milliseconds_pause);

        return $this;
    }

    public function visitAndWaitForText(string $uri, string $string): Browser
    {
        return $this->visit($uri)->waitForText($string);
    }

    public function pauseWhenRecording(int $seconds = 2): self
    {
        if (env('DUSK_RECORDING')) {
            return $this->pause($seconds * 1000);
        }

        return $this;
    }

    public function shortPause(int $milliseconds = 300): self
    {
        return $this->pause($milliseconds);
    }
}
