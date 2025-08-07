<?php

namespace Tests\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\AutomationsServiceProvider;
use App\Modules\Automations\src\Conditions\Order\ShippingMethodCodeNotInCondition;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsOnSpecificOrderJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Tests\TestCase;

class OrderShippingMethodCodeNotInConditionTest extends TestCase
{
    public function test_condition(): void
    {
        AutomationsServiceProvider::enableModule();

        $automation = Automation::create([
            'enabled' => true,
            'name' => 'change status',
        ]);

        Condition::create([
            'automation_id' => $automation->getKey(),
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'paid',
        ]);

        Condition::create([
            'automation_id' => $automation->getKey(),
            'condition_class' => ShippingMethodCodeNotInCondition::class,
            'condition_value' => 'dhl,ups',
        ]);

        Action::create([
            'automation_id' => $automation->getKey(),
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'processed',
        ]);

        $order = Order::factory()->create(['status_code' => 'paid', 'shipping_method_code' => 'store_pickup']);
        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        RunEnabledAutomationsOnSpecificOrderJob::dispatch($order->getKey());

        $this->assertEquals('processed', $order->refresh()->status_code);
    }
}
