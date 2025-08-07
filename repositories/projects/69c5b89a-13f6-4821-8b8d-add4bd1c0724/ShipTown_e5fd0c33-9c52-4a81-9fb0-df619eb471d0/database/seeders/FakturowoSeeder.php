<?php

namespace Database\Seeders;

use App\Modules\Fakturowo\src\Models\FakturowoConfiguration;
use Illuminate\Database\Seeder;

class FakturowoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (empty(env('TEST_FAKTUROWO_API_KEY'))) {
            return;
        }

        FakturowoConfiguration::query()->create([
            'api_key' => env('TEST_FAKTUROWO_API_KEY'),
            'connection_code' => 'test-account',
        ]);
    }
}
