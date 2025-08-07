<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('modules_automations_available_conditions')->updateOrInsert(
            ['class' => \App\Modules\Automations\src\Conditions\Order\ShippingMethodCodeNotInCondition::class],
            ['description' => 'Shipping Method Code is not in']
        );

        DB::table('modules_automations_available_conditions')->updateOrInsert(
            ['class' => \App\Modules\Automations\src\Conditions\Order\ShippingMethodNameNotInCondition::class],
            ['description' => 'Shipping Method Name is not in']
        );
    }

    public function down(): void
    {
        DB::table('modules_automations_available_conditions')
            ->whereIn('class', [
                \App\Modules\Automations\src\Conditions\Order\ShippingMethodCodeNotInCondition::class,
                \App\Modules\Automations\src\Conditions\Order\ShippingMethodNameNotInCondition::class,
            ])
            ->delete();
    }
};
