<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_collections', function (Blueprint $table) {
            $table->boolean('recount_required')->default(true)->after('currently_running_task');
            $table->timestamp('calculated_at')->nullable()->after('recount_required');
        });
    }
};
