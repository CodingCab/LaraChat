<?php

namespace Tests\Browser\Routes\Tools;

use Facebook\WebDriver\Exception\ElementClickInterceptedException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Tests\DuskTestCase;

class PicklistPageTest extends DuskTestCase
{
    private string $uri = '/tools/picklist';

    /**
     * @throws ElementClickInterceptedException
     * @throws NoSuchElementException
     */
    public function testPicklistPage(): void
    {
        $this->visit('dashboard');

        $this->startRecording('How to pick products?');
        $this->say('Hi Guys! Welcome to the Picklist Page!');

        $this->pause();

        $this->clickButton('#tools_link');
        $this->clickButton('#picklist_link');

        $this->pause();

        $this->browser()
            ->screenshot('picklist-link')
            ->waitForText('Status: paid')
            ->clickLink('Status: paid')
            ->pause($this->shortDelay)
            ->pause(2000);

        $this->browser()
            ->waitFor('@barcode-input-field')
            ->pause($this->shortDelay)
            ->keys('@barcode-input-field', '1234')
            ->pause($this->shortDelay)
            ->keys('@barcode-input-field', '{enter}')
            ->pause($this->longDelay);

        $this->browser()
            ->pause(1000)
            ->waitFor('@barcode-input-field')
            ->waitUntilMissing('.loading')
            ->waitUntilMissing('.spinner')
            ->script("document.querySelector('[dusk=\"barcode-input-field\"]').scrollIntoView()");

        $this->browser()
            ->pause($this->shortDelay)
            ->click('@barcode-input-field')
            ->pause($this->shortDelay)
            ->keys('@barcode-input-field', '1234')
            ->pause($this->shortDelay)
            ->keys('@barcode-input-field', '{enter}')
            ->pause($this->longDelay);
    }
}
