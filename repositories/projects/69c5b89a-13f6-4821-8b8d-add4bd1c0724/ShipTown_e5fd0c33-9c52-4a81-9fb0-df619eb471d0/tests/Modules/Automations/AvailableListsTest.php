<?php

namespace Tests\Modules\Automations;

use App\Modules\Automations\src\Models\AvailableAction;
use App\Modules\Automations\src\Models\AvailableCondition;
use App\Modules\Automations\src\Services\AutomationService;
use Tests\TestCase;

class AvailableListsTest extends TestCase
{
    public function test_lists_are_loaded_from_database(): void
    {
        $this->assertNotEmpty(AvailableCondition::all());
        $this->assertTrue(
            AvailableCondition::query()
                ->where('class', '=', \App\Modules\Automations\src\Conditions\Order\OriginStatusCodeEqualsCondition::class)
                ->exists()
        );
        $this->assertTrue(
            AvailableCondition::query()
                ->where('class', '=', \App\Modules\Automations\src\Conditions\Order\OriginStatusCodeInCondition::class)
                ->exists()
        );
        $this->assertEquals(
            AvailableCondition::query()->pluck('class')->sort()->values()->toArray(),
            AutomationService::availableConditions()->pluck('class')->sort()->values()->toArray()
        );

        $this->assertNotEmpty(AvailableAction::all());
        $this->assertEquals(
            AvailableAction::query()->pluck('class')->sort()->values()->toArray(),
            AutomationService::availableActions()->pluck('class')->sort()->values()->toArray()
        );
    }
}
