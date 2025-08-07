<?php

namespace Tests\Modules\Automations\Conditions;

use App\Models\ModelTag;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\DoesntHaveTagsCondition;
use App\Modules\Automations\src\Conditions\Order\HasTagsCondition;
use App\Modules\Automations\src\Jobs\RunAutomationJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Tests\TestCase;

class DoesntHaveTagsConditionTest extends TestCase
{
    public function test_if_works_when_tag_is_not_attached()
    {
        $order = Order::factory()->create(['status_code' => 'pending']);
        OrderProduct::factory()->create([
            'order_id' => $order->id,
        ]);

        $order->attachTags(['tag1', 'tag2']);

        // create new order and automation which will move the order to different status when has all the tags specified
        $automation = Automation::query()->create([
            'name' => 'Test Automation',
            'enabled' => true,
        ]);

        Condition::query()->create([
            'automation_id' => $automation->id,
            'condition_class' => DoesntHaveTagsCondition::class,
            'condition_value' => 'tag3',
        ]);

        Action::query()->create([
            'automation_id' => $automation->id,
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'completed',
        ]);


        ray()->showQueries();
        RunAutomationJob::dispatch($automation->id);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status_code' => 'completed',
        ]);
    }

    public function test_if_works_when_tag_is_attached()
    {
        $order = Order::factory()->create(['status_code' => 'pending']);
        OrderProduct::factory()->create([
            'order_id' => $order->id,
        ]);

        $order->attachTags(['tag3']);

        // create new order and automation which will move the order to different status when has all the tags specified
        $automation = Automation::query()->create([
            'name' => 'Test Automation',
            'enabled' => true,
        ]);

        Condition::query()->create([
            'automation_id' => $automation->id,
            'condition_class' => DoesntHaveTagsCondition::class,
            'condition_value' => 'tag3',
        ]);

        Action::query()->create([
            'automation_id' => $automation->id,
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'completed',
        ]);


        ray()->showQueries();
        RunAutomationJob::dispatch($automation->id);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status_code' => 'pending',
        ]);
    }
}
