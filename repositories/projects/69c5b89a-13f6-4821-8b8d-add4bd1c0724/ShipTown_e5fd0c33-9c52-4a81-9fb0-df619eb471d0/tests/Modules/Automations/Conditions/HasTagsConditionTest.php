<?php

namespace Tests\Modules\Automations\Conditions;

use App\Models\ModelTag;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\HasTagsCondition;
use App\Modules\Automations\src\Jobs\RunAutomationJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HasTagsConditionTest extends TestCase
{
    #[Test]
    public function test_if_works()
    {
        // create new order and automation which will move the order to different status when has all the tags specified
        $automation = Automation::query()->create([
            'name' => 'Test Automation',
            'enabled' => true,
        ]);

        Condition::query()->create([
            'automation_id' => $automation->id,
            'condition_class' => HasTagsCondition::class,
            'condition_value' => 'tag1,tag2',
        ]);

        Action::query()->create([
            'automation_id' => $automation->id,
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'completed',
        ]);

        $order = Order::factory()->create(['status_code' => 'pending']);
        OrderProduct::factory()->create([
            'order_id' => $order->id,
        ]);

        ray()->showQueries();
        $order->attachTags(['tag1', 'tag2']);

        RunAutomationJob::dispatch($automation->id);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status_code' => 'completed',
        ]);
    }
}
