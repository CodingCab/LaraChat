<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Module::query()
            ->whereNull('settings_link')
            ->get()
            ->each(function (Module $module) {
                $module->settings_link = $module->settings_link;
                $module->save();
            });
    }
};
