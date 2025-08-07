<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderAddress;
use Illuminate\Database\Seeder;

class InPostPolandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shippingAddress = OrderAddress::factory()->create([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'address1' => 'ul. Testowa 1',
            'city' => 'Warszawa',
            'postcode' => '00-001',
            'country_code' => 'POL',
            'country_name' => 'Polska',
            'phone' => '+48123456789',
            'locker_box_code' => 'ORZ09M',
        ]);

        /** @var Order $order */
        $order = Order::factory()->create([
            'order_number' => 'INPOST-123456789',
            'status_code' => 'paid',
            'shipping_method_code' => 'inpost_paczkomaty_gabaryt_a',
            'shipping_method_name' => 'inpost_paczkomaty_gabaryt_a',
            'label_template' => 'inpost_paczkomaty_gabaryt_a',
            'shipping_address_id' => $shippingAddress->getKey(),
        ]);
    }
}
