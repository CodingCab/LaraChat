<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Module::query()
            ->whereNull('name')
            ->orWhereNull('description')
            ->get()
            ->each(function (Module $module) {
                $module->name = $module->name;
                $module->description = $module->description;
                $module->save();
            });
    }
};
