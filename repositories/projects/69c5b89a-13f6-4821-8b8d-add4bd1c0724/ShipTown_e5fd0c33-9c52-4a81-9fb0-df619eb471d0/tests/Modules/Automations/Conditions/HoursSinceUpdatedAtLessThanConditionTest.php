<?php

namespace Tests\Modules\Automations\Conditions;

use App\Models\Order;
use App\Modules\Automations\src\Conditions\Order\HoursSinceLastUpdatedAtLessThanCondition;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HoursSinceUpdatedAtLessThanConditionTest extends TestCase
{
    public function testConditionTrue(): void
    {
        $select = DB::select('select now() as now');

        Order::factory()->create(['updated_at' => Carbon::createFromTimeString($select[0]->now)->subHour()]);

        $query = Order::query();
        HoursSinceLastUpdatedAtLessThanCondition::addQueryScope($query, '2');
        $this->assertEquals(1, $query->count(), 'We expect 1 order to be returned');
    }

    public function testConditionFalse(): void
    {
        $select = DB::select('select now() as now');

        Order::factory()->create(['updated_at' => Carbon::createFromTimeString($select[0]->now)->subHours(3)]);

        $query = Order::query();
        HoursSinceLastUpdatedAtLessThanCondition::addQueryScope($query, '2');
        $this->assertEquals(0, $query->count(), 'We expect 0 orders to be returned');
    }
}
