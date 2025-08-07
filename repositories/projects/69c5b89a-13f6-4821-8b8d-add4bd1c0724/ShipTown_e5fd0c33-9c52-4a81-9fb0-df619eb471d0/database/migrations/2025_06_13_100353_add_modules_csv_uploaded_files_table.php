<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules_csv_uploaded_files', function (Blueprint $table) {
            $table->id();
            $table->timestamp('processed_at')->nullable();
            $table->string('filename')->nullable();
            $table->longText('file_content')->nullable()->charset('binary');
            $table->json('mapped_fields')->nullable();
            $table->unsignedInteger('processed_records')->default(0);
            $table->unsignedInteger('invalid_records')->default(0);
            $table->timestamps();
        });
    }
};
