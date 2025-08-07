<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('modules_automations_available_conditions')->updateOrInsert(
            ['class' => \App\Modules\Automations\src\Conditions\Order\BillingAddressHasTaxIdCondition::class],
            ['description' => 'Billing address has tax number']
        );
    }
};
