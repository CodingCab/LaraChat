<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('models_tags', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->string('model_id', 20);
            $table->string('tag_type', 50)->nullable();
            $table->string('tag_name', 100);
            $table->timestamps();
        });

        Schema::table('models_tags', function (Blueprint $table) {
            $table->unique(['model_type', 'model_id', 'tag_type', 'tag_name'], 'model_tag_unique');
            $table->index(['model_type', 'model_id', 'tag_type'], 'model_tag_type_index');
            $table->index(['tag_type']);
            $table->index(['tag_name']);
        });
    }
};
