<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaidTodaysOrdersFromStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory(10)->create([
            'order_placed_at' => now()->subHour(),
            'status_code' => 'paid',
        ])
        ->each(function (Order $order) {
            Inventory::where('quantity_available', '>', 0)
                ->limit(rand(1, 3))
                ->get()->each(function (Inventory $inventory) use ($order) {
                    OrderProduct::factory()->create([
                        'order_id' => $order->getKey(),
                        'product_id' => $inventory->product_id,
                        'sku_ordered' => $inventory->product->sku,
                        'name_ordered' => $inventory->product->name,
                        'price' => $inventory->prices->first()->price,
                        'quantity_ordered' => rand(1, $inventory->quantity_available),
                        'quantity_shipped' => 0,
                    ]);
                });

            $order->update(['total_paid' => $order->orderProducts()->sum('total_price')]);
        });
    }
}
