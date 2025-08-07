<?php

namespace Tests\Browser\Routes;

use Tests\DuskTestCase;
use Throwable;

class NavigationBarProductsLinksTest extends DuskTestCase
{
    private string $uri = '/dashboard';

    /**
     * @throws Throwable
     */
    public function testInventoryLinkHasHref(): void
    {
        $this->testUser->assignRole('admin');
        
        $this->browser()
            ->loginAs($this->testUser)
            ->visit($this->uri);

        $this->clickButton('#products_link');

        $this->browser()->assertAttribute('#inventory_link', 'href', '/products/inventory?sort=-quantity');
    }

    /**
     * Test that all products dropdown links have href attributes for middle-click support
     * @throws Throwable
     */
    public function testAllProductsLinksHaveHref(): void
    {
        $this->testUser->assignRole('admin');
        
        $this->browser()
            ->loginAs($this->testUser)
            ->visit($this->uri);

        $this->clickButton('#products_link');

        // Test all expected product menu links have href attributes
        $expectedLinks = [
            '#inventory_link' => '/products/inventory?sort=-quantity',
            '#transfers_in_link' => '/products/transfers-in?filter[type]=App\\Models\\DataCollectionTransferIn',
            '#transfers_out_link' => '/products/transfers-out?filter[type]=App\\Models\\DataCollectionTransferOut',
            '#purchases_order_link' => '/products/purchase-orders?filter[type]=App\\Models\\DataCollectionPurchaseOrder',
            '#transactions_link' => '/products/transactions?filter[type]=App\\Models\\DataCollectionTransaction',
            '#stocktaking_link' => '/products/stocktaking?'
        ];

        foreach ($expectedLinks as $selector => $expectedHref) {
            $this->browser()->assertAttribute($selector, 'href', $expectedHref);
        }
    }
}
