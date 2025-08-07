<?php

namespace Tests\Feature;

use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\StatusCodeInCondition;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FlowchartSampleDynamicTest extends TestCase
{
    #[Test]
    public function chart_contains_automation_transitions(): void
    {
        $this->actingAsUser();

        $automation = Automation::factory()->create(['name' => 'Demo', 'enabled' => true]);
        Condition::factory()->create([
            'automation_id' => $automation->getKey(),
            'condition_class' => StatusCodeInCondition::class,
            'condition_value' => 'new',
        ]);
        Action::factory()->create([
            'automation_id' => $automation->getKey(),
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'processing',
        ]);

        $response = $this->get('/flowchart-sample');

        ray($response->getContent());
        $response->assertOk();
        $response->assertSee('flowchart TD');
        $response->assertSee('new --> processing', false);
    }

    #[Test]
    public function chart_uses_provided_type_parameter(): void
    {
        $this->actingAsUser();

        $response = $this->get('/flowchart-sample?type=LR');

        $response->assertOk();
        $response->assertSee('flowchart LR');
    }
}
