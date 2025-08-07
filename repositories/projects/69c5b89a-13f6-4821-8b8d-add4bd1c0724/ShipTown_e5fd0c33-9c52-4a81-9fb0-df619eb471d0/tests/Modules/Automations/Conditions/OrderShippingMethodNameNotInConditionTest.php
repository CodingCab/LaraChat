<?php

namespace Tests\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\AutomationsServiceProvider;
use App\Modules\Automations\src\Conditions\Order\ShippingMethodNameNotInCondition;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsOnSpecificOrderJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Tests\TestCase;

class OrderShippingMethodNameNotInConditionTest extends TestCase
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
            'condition_class' => ShippingMethodNameNotInCondition::class,
            'condition_value' => 'Next Day Delivery,Express Delivery',
        ]);

        Action::create([
            'automation_id' => $automation->getKey(),
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'processed',
        ]);

        $order = Order::factory()->create([
            'status_code' => 'paid',
            'shipping_method_name' => 'Standard Shipping',
        ]);
        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        RunEnabledAutomationsOnSpecificOrderJob::dispatch($order->getKey());

        $this->assertEquals('processed', $order->refresh()->status_code);
    }
}
