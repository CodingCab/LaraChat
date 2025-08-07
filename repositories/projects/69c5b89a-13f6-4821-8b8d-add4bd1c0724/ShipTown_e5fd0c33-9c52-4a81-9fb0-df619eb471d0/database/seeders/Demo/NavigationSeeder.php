<?php

namespace Database\Seeders\Demo;

use App\Models\NavigationMenu;
use Illuminate\Database\Seeder;

class NavigationSeeder extends Seeder
{
    public function run(): void
    {
        NavigationMenu::query()->create([
            'name' => '7 Day - Best Sellers',
            'url' => '/reports/inventory?filter%5Bwarehouse_code%5D=DUB&sort=-last_7_days_sales',
            'group' => 'reports'
        ]);
    }
}
