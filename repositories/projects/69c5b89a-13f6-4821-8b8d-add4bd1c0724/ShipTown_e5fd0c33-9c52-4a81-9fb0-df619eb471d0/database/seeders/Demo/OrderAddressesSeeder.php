<?php

namespace Database\Seeders\Demo;

use App\Models\OrderAddress;
use Illuminate\Database\Seeder;

class OrderAddressesSeeder extends Seeder
{
    public function run(): void
    {
        OrderAddress::factory()->create([
            'address1' => 'Złota 59',
            'postcode' => '00-120',
            'city' => 'Warszawa',
            'country_code' => 'PL',
            'country_name' => 'Poland',
            'state_name' => 'Mazowieckie',
            'tax_id' => '3928621931',
        ]);
    }
}
