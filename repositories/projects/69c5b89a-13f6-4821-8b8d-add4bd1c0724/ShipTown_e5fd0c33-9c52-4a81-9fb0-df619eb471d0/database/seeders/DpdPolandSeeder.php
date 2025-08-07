<?php

namespace Database\Seeders;

use App\Modules\Couriers\ShippyPro\DpdPoland\src\ShippyProDpdPolandServiceProvider;
use Illuminate\Database\Seeder;

class DpdPolandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (empty(env('SHIPPY_PRO_DPD_POLAND_PREFIXES'))) {
            return;
        }

        ShippyProDpdPolandServiceProvider::enableModule();
    }
}
