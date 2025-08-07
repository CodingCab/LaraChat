<?php

namespace Tests\Browser\Routes;

use Tests\DuskTestCase;
use Throwable;

class SettingsModalTest extends DuskTestCase
{
    /**
     * @throws Throwable
     */
    public function test_modal_opens_from_navigation(): void
    {
        $this->visit('/dashboard', $this->testAdmin);

        $this->clickButton('#dropdownMenu');
        $this->clickButton('#menu_settings_link');

        $this->browser()->waitFor('#settings-modal');
    }
}
