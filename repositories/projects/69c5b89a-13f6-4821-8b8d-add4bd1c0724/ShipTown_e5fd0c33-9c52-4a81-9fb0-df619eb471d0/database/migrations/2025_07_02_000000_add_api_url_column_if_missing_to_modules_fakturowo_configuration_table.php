<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('modules_fakturowo_configuration', 'api_url')) {
            Schema::table('modules_fakturowo_configuration', function (Blueprint $table) {
                $table->string('api_url')->nullable()->after('connection_code');
            });
        }
    }
};
