<?php

namespace Tests\Browser\Routes\Tools;

use Database\Seeders\Demo\ProductsSeeder;
use Tests\DuskTestCase;

class ShelfLabelsPageTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ProductsSeeder::class);
    }

    public function testPage()
    {
        $this->visit('/dashboard');

        $this->startRecording('How to use smart shelf labels?');

        $this->say('Hi Guys! I will demonstrate how to use our smart shelf labels');
        $this->say('Let\'s start from the top menu, click on Tools > Shelf Labels');

        $this->clickButton('#tools_link')
            ->clickButton('#shelf_labels_link');

        $this->say('You can see the template dropdown on the left side of the screen, where you can choose the template for your shelf labels');
        $this->clickButton('#template_select_dropdown')
            ->clickButton('#template_select_dropdown-option-1')
            ->clickButton('#template_select_dropdown')
            ->clickButton('#template_select_dropdown-option-2')
            ->clickButton('#template_select_dropdown')
            ->clickButton('#template_select_dropdown-option-3')
            ->pause();

        $this->say('To create a custom shelf label, type the label name in the input field and click enter');
        $this->say('It can be any text you want, for example, you can use shelf location name');
        $this->typeAndEnter('BESIDE DOOR')
            ->pause();

        $this->say('I will demonstrate how to use our smart shelf labels on products page');

        $this->clickButton('#products_link')
            ->clickButton('#inventory_link')
            ->typeAndEnter('4001');

        $this->say('To assign shelf location to a product, scan our smart shelf label');
        $this->typeAndEnter('shelf:A1');
        $this->say('then scan the product itself');
        $this->typeAndEnter('4001');

        $this->say('We designed our smart shelf labels to be user-friendly and easy to use without taking off your finger from the scanner. Scan shelf label, scan product, and repeat.');
        $this->typeAndEnter('shelf:BESIDE DOOR')
            ->typeAndEnter('4001')
            ->pause();

        $this->say('In more advanced scenarios, you can use continues scan to update multiple products in a row. To do that, scan the shelf label twice');
        $this->typeAndEnter('shelf:B6');
        $this->pause();
        $this->typeAndEnter('shelf:B6');
        $this->say('Now you can scan multiple products in a row');
        $this->typeAndEnter('4001')
            ->typeAndEnter('4002')
            ->typeAndEnter('4003')
            ->pause();

        $this->say('To finish the process, scan the shelf label again');
        $this->typeAndEnter('shelf:B6')
            ->pause();

        $this->say('That\'s it! You now know how to use our smart shelf labels and how to assign products to shelf locations');
        $this->pause(2);

        $this->stopRecording();
    }
}
