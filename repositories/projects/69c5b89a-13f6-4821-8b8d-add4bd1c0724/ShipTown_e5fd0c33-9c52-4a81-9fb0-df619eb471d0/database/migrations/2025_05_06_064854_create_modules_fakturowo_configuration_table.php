<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules_fakturowo_configuration', function (Blueprint $table) {
            $table->id();
            $table->string('connection_code')->nullable();
            $table->longText('api_key_encrypted')->nullable();
            $table->timestamps();
        });
    }
};
