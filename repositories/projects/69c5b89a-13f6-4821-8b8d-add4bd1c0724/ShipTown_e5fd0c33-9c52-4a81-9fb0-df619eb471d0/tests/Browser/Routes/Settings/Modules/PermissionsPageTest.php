<?php

namespace Tests\Browser\Routes\Settings\Modules;

use App\Modules\Permissions\src\Models\Permission;
use App\Modules\Permissions\src\Models\Role;
use App\Modules\Permissions\src\PermissionsModuleServiceProvider;
use Tests\DuskTestCase;
use Throwable;

class PermissionsPageTest extends DuskTestCase
{
    /**
     * @throws Throwable
     */
    public function testPage()
    {
        $this->testUser->assignRole('admin');

        $this->browser()
            ->loginAs($this->testUser)
            ->visit('dashboard');

        $this->startRecording('How to manage a permissions?');

        $this->say('Hi Guys! I will demonstrate how to manage user permissions.');
        $this->say('From the top menu click on Hamburger Menu > Settings.');

        $this->clickButton('#dropdownMenu');
        $this->clickButton('#menu_settings_link');

        $this->say('From the list of the options click on Modules.');
        $this->clickButton('@goToModulesPage');

        $this->say('You can see the list of modules. At the top of the list, you can see the Permissions module.' .
            'Click the cog icon to manage permissions.');
        $this->clickButton('@settingsModulesPermissions');

        $this->say('You can see the list of permissions. You can search for a specific permission by typing in the search field.');
        $this->say('For this example, let\'s assume that users with User role shouldn\'t be able to change product description.');
        $this->say('Type "product.description" in search bar and press ENTER button.');

        $this->typeSlowly('@barcode-input-field', 'product.description');
        $this->clickEnter();
        $this->pause(1);

        $this->browser()->assertSee('api.product.descriptions.store');

        $this->say('Now, you can see the  "api.product.description.store" permission.');
        $this->say('To block the possibility to change product description, click on checkbox to change the state.');

        $this->browser()->click('input[name^="api.product.descriptions.store"][data-role="user"]');

        $this->say('That\'s it! Now users with User role won\'t be able to change product description.');

        $this->stopRecording();
    }

    protected function setUp(): void
    {
        parent::setUp();

        PermissionsModuleServiceProvider::enableModule();

        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->givePermissionTo(Permission::firstOrCreate(['name' => 'api.product.descriptions.store']));
    }
}
