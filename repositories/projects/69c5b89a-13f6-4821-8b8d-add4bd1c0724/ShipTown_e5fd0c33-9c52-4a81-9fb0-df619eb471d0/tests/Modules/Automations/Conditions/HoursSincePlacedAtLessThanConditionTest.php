<?php

namespace Tests\Modules\Automations\Conditions;

use App\Models\Order;
use App\Modules\Automations\src\Conditions\Order\HoursSincePlacedAtLessThanCondition;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HoursSincePlacedAtLessThanConditionTest extends TestCase
{
    public function testConditionTrue(): void
    {
        $select = DB::select('select now() as now');

        Order::factory()->create(['order_placed_at' => Carbon::createFromTimeString($select[0]->now)->subHour()]);

        $query = Order::query();
        HoursSincePlacedAtLessThanCondition::addQueryScope($query, '2');
        $this->assertEquals(1, $query->count(), 'We expect 1 order to be returned');
    }

    public function testConditionFalse(): void
    {
        $select = DB::select('select now() as now');

        Order::factory()->create(['order_placed_at' => Carbon::createFromTimeString($select[0]->now)->subHours(3)]);

        $query = Order::query();
        HoursSincePlacedAtLessThanCondition::addQueryScope($query, '2');
        $this->assertEquals(0, $query->count(), 'We expect 0 orders to be returned');
    }
}
