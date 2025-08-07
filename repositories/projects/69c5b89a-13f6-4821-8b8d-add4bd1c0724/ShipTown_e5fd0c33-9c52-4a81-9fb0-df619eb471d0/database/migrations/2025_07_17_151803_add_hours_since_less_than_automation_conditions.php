<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add new "less than" conditions for automation
        $conditions = [
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\HoursSinceLastUpdatedAtLessThanCondition::class,
                'description' => 'Hours Since Last Updated less than',
            ],
            [
                'class' => \App\Modules\Automations\src\Conditions\Order\HoursSincePlacedAtLessThanCondition::class,
                'description' => 'Hours Since Placed less than',
            ],
        ];

        foreach ($conditions as $condition) {
            DB::table('modules_automations_available_conditions')->updateOrInsert(
                ['class' => $condition['class']],
                ['description' => $condition['description']]
            );
        }
    }

    public function down(): void
    {
        // Remove the conditions
        DB::table('modules_automations_available_conditions')
            ->whereIn('class', [
                \App\Modules\Automations\src\Conditions\Order\HoursSinceLastUpdatedAtLessThanCondition::class,
                \App\Modules\Automations\src\Conditions\Order\HoursSincePlacedAtLessThanCondition::class,
            ])
            ->delete();
    }
};