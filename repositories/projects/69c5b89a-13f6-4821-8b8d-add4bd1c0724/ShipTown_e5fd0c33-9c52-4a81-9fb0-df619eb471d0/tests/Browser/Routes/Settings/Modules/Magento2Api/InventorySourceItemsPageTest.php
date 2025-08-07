<?php

namespace Tests\Browser\Routes\Settings\Modules\Magento2Api;

use App\Modules\Magento2API\InventorySync\src\Models\Magento2msiConnection;
use App\Modules\Magento2API\InventorySync\src\Models\Magento2msiProduct;
use App\User;
use Database\Factories\Modules\Magento2API\InventorySync\src\Models\Magento2msiConnectionFactory;
use Tests\DuskTestCase;
use Throwable;

class InventorySourceItemsPageTest extends DuskTestCase
{
    private string $uri = '/settings/modules/magento2api/inventory-source-items';

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

    public function testFilter()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $query = [
            'filter[search_contains]' => 'test-sku',
            'filename' => 'data.json',
            'page' => 1,
            'per_page' => 10,
        ];
        $uri = $this->uri . '?' . http_build_query($query);

        $response = $this->actingAs($user)->getJson($uri);

        $response->assertOk();
    }
}
