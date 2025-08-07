<?php

namespace Tests\Browser\Routes\Settings\Modules;

use App\Modules\PointOfSaleConfiguration\src\PointOfSaleConfigurationServiceProvider;
use Tests\DuskTestCase;

class PointOfSaleConfigurationPageTest extends DuskTestCase
{
    private string $uri = '/settings/modules/point-of-sale-configuration';

    public function testPage()
    {
        $this->testUser->assignRole('admin');

        $this->browser()
            ->loginAs($this->testUser)
            ->visit('dashboard');

        $this->startRecording('How to configure Point of Sale?');

        $this->say('Hi Guys! I will demonstrate how to configure Point of Sale.');
        $this->say('From the top menu click on Hamburger Menu > Settings.');

        $this->clickButton('#dropdownMenu');
        $this->pause();
        $this->clickButton('#menu_settings_link');
        $this->pause();

        $this->say('From the list of the options click on Modules.');
        $this->clickButton('@goToModulesPage');
        $this->pause();

        $this->say('You can see the list of modules. At the top of the list, you can see the Point Of Sale Configuration module.' .
            'Click the cog icon to configure PoS.');
        $this->clickButton('@settingsModulesPoint-of-sale-configuration');

        $this->pause(1);

        $this->say('Currently there are only one thing that you can configure - the "Next Transaction Number".');
        $this->say('By default, it is set to 1. You can change it to any number you want.');

        $this->pause(1);

        $this->browser()->assertSee('Next Transaction Number');

        $this->browser()->keys('#next_transaction_number', '{backspace}');
        $this->browser()->keys('#next_transaction_number', '1500');
        $this->pause(1);

        $this->clickButton('@saveButton');
        $this->say('Next transaction number is now set to 1500 and it will be automatically incremented when one of the ongoing transactions will be finished.');

        $this->pause(1);
        $this->stopRecording();
    }

    protected function setUp(): void
    {
        parent::setUp();

        PointOfSaleConfigurationServiceProvider::enableModule();
    }
}
