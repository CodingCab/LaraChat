<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_collection_records', function (Blueprint $table) {
            if (!Schema::hasColumn('data_collection_records', 'is_reserved')) {
                $table->boolean('is_reserved')->after('is_processed')->nullable();
            }
        });
    }
};
