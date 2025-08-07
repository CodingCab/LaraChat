<?php

use App\Modules\ScheduledReport\src\ScheduledReportModulesServiceProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules_scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('uri');
            $table->string('email');
            $table->string('cron');
            $table->timestamp('next_run_at');
            $table->timestamps();
        });

        ScheduledReportModulesServiceProvider::installModule();
    }
};
