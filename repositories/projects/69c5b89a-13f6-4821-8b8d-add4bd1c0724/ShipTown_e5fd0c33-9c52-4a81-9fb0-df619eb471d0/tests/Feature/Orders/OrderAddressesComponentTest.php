<?php

namespace Tests\Feature\Orders;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderAddressesComponentTest extends TestCase
{
    #[Test]
    public function order_addresses_component_exists(): void
    {
        $this->assertFileExists(resource_path('js/components/Orders/OrderAddresses.vue'));
    }
}
