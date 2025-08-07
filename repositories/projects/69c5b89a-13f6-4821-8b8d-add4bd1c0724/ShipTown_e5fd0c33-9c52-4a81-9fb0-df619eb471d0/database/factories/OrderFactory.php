<?php

namespace Database\Factories;

use App\Models\OrderAddress;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeZone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $shippingAddress = OrderAddress::factory()->create(['country_name' => 'Ireland', 'country_code' => 'IE']);
        $billingAddress = OrderAddress::factory()->create(['country_name' => 'Ireland', 'country_code' => 'IE']);

        /** @var OrderStatus $orderStatus */
        $orderStatus = OrderStatus::query()->inRandomOrder()->first() ?? OrderStatus::factory()->create();

        do {
            try {
                $dateTime = $this->faker->dateTimeBetween('-4days', now());
                Carbon::parse($dateTime, new DateTimeZone('UTC'));
            } catch (InvalidFormatException $exception) {
                report($exception);
                $dateTime = null;
            }
        } while ($dateTime === null);

        $shippingMethod = Arr::random([
            [
                'code' => 'next_day',
                'name' => 'Next Day Delivery',
            ],
            [
                'code' => 'store_pickup',
                'name' => 'Store Pickup',
            ],
            [
                'code' => 'express',
                'name' => 'Express Delivery',
            ]
        ]);


        $newOrder = [
            'order_number' => (string) (10000000 + $this->faker->unique()->randomNumber(7)),
//            'order_products' => function () {
//                return OrderProduct::factory()->make();
//            },
            'total_products' => $this->faker->randomNumber(2),
            'total_shipping' => $this->faker->randomElement([5, 10, 15, 20]),
            'shipping_address_id' => $shippingAddress->getKey(),
            'billing_address_id' => $billingAddress->getKey(),
            'shipping_method_code' => $shippingMethod['code'],
            'shipping_method_name' => $shippingMethod['name'],
            'order_placed_at' => $dateTime,
            'status_code' => $orderStatus->code,
            'origin_status_code' => $orderStatus->code,
        ];

        if (! $orderStatus->order_active) {
            $newOrder['order_closed_at'] = $this->faker->dateTimeBetween($newOrder['order_placed_at'], now());
        }

        return $newOrder;
    }
}
