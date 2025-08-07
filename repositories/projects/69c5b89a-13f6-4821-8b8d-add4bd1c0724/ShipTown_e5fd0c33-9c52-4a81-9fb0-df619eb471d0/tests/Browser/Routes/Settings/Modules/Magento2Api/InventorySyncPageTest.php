<?php

namespace Tests\Browser\Routes\Settings\Modules\Magento2Api;

use App\User;
use Tests\DuskTestCase;
use Throwable;

class InventorySyncPageTest extends DuskTestCase
{
    private string $uri = '/settings/modules/magento2api/inventory-sync';

    /**
     * @throws Throwable
     */
    public function testPage()
    {
        /** @var User $user */
        $user = User::factory()->create();
         $user->assignRole('admin');

        $this->visit($this->uri, $user);
        $this->browser()
            ->assertPathIs($this->uri);
    }
}
