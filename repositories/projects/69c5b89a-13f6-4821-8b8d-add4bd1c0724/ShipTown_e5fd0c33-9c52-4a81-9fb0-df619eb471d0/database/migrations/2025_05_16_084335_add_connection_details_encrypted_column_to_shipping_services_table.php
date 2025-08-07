<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_services', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_services', 'connection_details_encrypted')) {
                $table->longText('connection_details_encrypted')->nullable()->after('service_provider_class');
            }
        });
    }
};
