<?php

use App\Modules\AutoStatusRefill\src\Models\Automation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules_autostatus_picking_configurations', function (Blueprint $table) {
            $table->string('from_status_code')->nullable()->after('id');
            $table->string('to_status_code')->nullable()->after('from_status_code');
            $table->integer('desired_order_count')->default(10)->after('to_status_code');
            $table->boolean('refill_only_at_0')->default(true)->after('desired_order_count');

        });

        Automation::query()->update([
            'from_status_code' => 'paid',
            'to_status_code' => 'picking',
            'desired_order_count' => DB::raw('max_batch_size'),
            'refill_only_at_0' => true,
        ]);

        Schema::table('modules_autostatus_picking_configurations', function (Blueprint $table) {
            $table->dropColumn('max_order_age');
            $table->dropColumn('max_batch_size');
        });

        Schema::table('modules_autostatus_picking_configurations', function (Blueprint $table) {
            $table->string('from_status_code')->nullable(false)->change();
            $table->string('to_status_code')->nullable(false)->change();
        });
    }
};
