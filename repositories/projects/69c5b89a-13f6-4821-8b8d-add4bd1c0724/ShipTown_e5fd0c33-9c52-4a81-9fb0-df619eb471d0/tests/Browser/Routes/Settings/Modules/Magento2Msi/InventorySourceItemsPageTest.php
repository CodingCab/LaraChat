<?php

namespace Tests\Browser\Routes\Settings\Modules\Magento2Msi;

use App\User;
use Tests\DuskTestCase;
use Throwable;

class InventorySourceItemsPageTest extends DuskTestCase
{
    private string $uri = '/settings/modules/magento2msi/inventory-source-items';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->visit($this->uri, $user);
        $this->browser()
            ->assertPathIs($this->uri);
    }
}
