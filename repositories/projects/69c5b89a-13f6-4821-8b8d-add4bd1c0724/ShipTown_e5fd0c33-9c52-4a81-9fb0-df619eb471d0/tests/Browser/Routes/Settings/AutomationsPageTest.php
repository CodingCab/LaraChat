<?php

namespace Tests\Browser\Routes\Settings;

use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class AutomationsPageTest extends DuskTestCase
{
    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        $browser = $this->browser();

        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->visit('/dashboard', $user);

        $this->startRecording('How to create order automations ?');

        $this->say('This tutorial will guide you through setting up an automation using the form.');
        $this->say('Let\'s start from the top menu, click on burger menu > Settings');
        $this->clickButton('#dropdownMenu')
            ->clickButton('#menu_settings_link');

        $this->say('Now, click on Order Automations');
        $this->clickButton('@order-automations-link');

        $this->say('You can see the list of created automations. To create a new automation, click on the "Add New" button');
        $this->clickButton('@add-new-button');

        $this->say('Here you can set the automation name, select the trigger, and set the action');
        $this->say('For example, I will create an automation that will set courier label to "raben_pallet" when the total weight of the order is more than 30 Kilograms');

        $this->say('Fill in the "Automation Name" field with a descriptive name.');
        $this->typeSlowly('#create-name', 'Heavy Order')->screenshot();

        $this->say('Toggle the "Enabled" switch if you want the automation to be active immediately.');
        $browser->check('@create-enabled');
        $this->screenshot()->pause();

        $this->say('You can provide additional details about what this automation does.');
        $this->typeSlowly('#create-description', 'Set courier label to "raben_pallet" when the total weight of the order is more than 30 Kilograms')
            ->screenshot();

        $this->say('Select a trigger, such as "Placed in Last 28 Days or Active Orders.');
        $this->clickButton('#create-event');
        $browser->select('#create-event', 'Placed in Last 28 Days or Active Orders');
        $this->screenshot();

        $this->say('Select condition to specify the criteria');
        $this->clickButton('#create-condition-0');
        $browser->select('#create-condition-0', 'App\Modules\Automations\src\Conditions\Order\OrderTotalWeightGreaterThanCondition');
        $this->clickButton('#create-condition-value-0');
        $browser->typeSlowly('#create-condition-value-0', '30');
        $this->screenshot();

        $this->say('Select action to specify what should happen when the condition is met');
        $this->clickButton('#create-action-0');
        $browser->select('#create-action-0', 'App\Modules\Automations\src\Actions\Order\SetLabelTemplateAction');
        $this->clickButton('#create-action-value-0');
        $browser->typeSlowly('#create-action-value-0', 'raben_pallet');
        $this->screenshot();

        $this->say('Click on the "Save" button to create the automation');
        $this->clickButton('@create-save-button');


        $this->pause(4);

        $this->stopRecording();
    }
}
