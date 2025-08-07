<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \App\Models\NavigationMenu::query()
            ->whereLike('url', '%autopilot%')
            ->each(function ($item) {
                $item->url = str_replace('autopilot', 'tools', $item->url);
                $item->save();
            });

        \App\Models\NavigationMenu::query()
            ->whereLike('url', '/picklist%')
            ->get()
            ->each(function ($item) {
                $item->url = str_replace('/picklist', '/tools/picklist', $item->url);
                $item->save();
            });
    }
};
