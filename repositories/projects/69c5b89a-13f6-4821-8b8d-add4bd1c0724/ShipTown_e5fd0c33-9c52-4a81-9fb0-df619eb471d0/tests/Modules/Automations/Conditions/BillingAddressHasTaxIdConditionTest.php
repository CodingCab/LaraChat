<?php

namespace Tests\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Modules\Automations\src\Conditions\Order\BillingAddressHasTaxIdCondition;
use Tests\TestCase;

class BillingAddressHasTaxIdConditionTest extends TestCase
{
    public function test_true_query_scope(): void
    {
        $addressWithTax = OrderAddress::factory()->create(['tax_id' => '123']);
        $orderWith = Order::factory()->create(['billing_address_id' => $addressWithTax->getKey()]);

        $addressWithoutTax = OrderAddress::factory()->create(['tax_id' => null]);
        Order::factory()->create(['billing_address_id' => $addressWithoutTax->getKey()]);

        $query = Order::query();
        BillingAddressHasTaxIdCondition::addQueryScope($query, 'true');

        $this->assertEquals([$orderWith->getKey()], $query->pluck('id')->all());
    }

    public function test_false_query_scope(): void
    {
        $addressWithTax = OrderAddress::factory()->create(['tax_id' => '123']);
        Order::factory()->create(['billing_address_id' => $addressWithTax->getKey()]);

        $addressWithoutTax = OrderAddress::factory()->create(['tax_id' => null]);
        $orderWithout = Order::factory()->create(['billing_address_id' => $addressWithoutTax->getKey()]);

        $query = Order::query();
        BillingAddressHasTaxIdCondition::addQueryScope($query, 'false');

        $this->assertEquals([$orderWithout->getKey()], $query->pluck('id')->all());
    }
}
