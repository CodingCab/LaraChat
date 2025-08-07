<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules_api2cart_call_responses', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('url');
            $table->dateTime('processed_at')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }
};
