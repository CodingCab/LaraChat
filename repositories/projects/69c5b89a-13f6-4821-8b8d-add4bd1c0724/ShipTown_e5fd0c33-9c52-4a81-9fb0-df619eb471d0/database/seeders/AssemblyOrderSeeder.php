<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Database\Seeder;

class AssemblyOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Order::query()->where('order_number', '#1232764-ASSEMBLY')->exists()) {
            return;
        }

        $order = Order::factory()->create([
            'order_number' => '#1232764-ASSEMBLY',
            'order_placed_at' => now()->subHours(3),
            'status_code' => 'paid',
            'shipping_method_code' => 'raben_3day',
            'label_template' => 'raben_3day'
        ]);

        $product1 = Product::factory()->create([
            'sku' => 'KULSR120_45',
            'name' => 'Stół rozkładany prostokątny 120cm dębowy Orbetello',
//            'description' => 'https://meblekatmandu.pl/stol-rozkladany-prostokatny-120cm-debowy-orbetello',
        ]);

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'sku_ordered' => $product1->sku,
            'quantity_ordered' => 1,
            'quantity_shipped' => 0,
        ]);

        $product2 = Product::factory()->create([
            'sku' => 'KULSR120_45-01',
            'name' => 'Stół rozkładany prostokątny 120cm dębowy Orbetello - Paczka-01 Blat',
//            'description' => 'https://meblekatmandu.pl/stol-rozkladany-prostokatny-120cm-debowy-orbetello',
        ]);

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'sku_ordered' => $product2->sku,
            'quantity_ordered' => 1,
            'quantity_shipped' => 0,
            'price' => 3999
        ]);

        $product3 = Product::factory()->create([
            'sku' => 'KULSR120_45-02',
            'name' => 'Stół rozkładany prostokątny 120cm dębowy Orbetello - Paczka-01 Nogi dębowe x4 ',
//            'description' => 'https://meblekatmandu.pl/stol-rozkladany-prostokatny-120cm-debowy-orbetello',
        ]);

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'sku_ordered' => $product3->sku,
            'quantity_ordered' => 1,
            'quantity_shipped' => 0,
            'price' => 649
        ]);

        $product4 = Product::factory()->create([
            'sku' => 'KDB02BR',
            'name' => 'Krzesło dębowe z brązowym siedziskiem Verto',
//            'description' => 'https://meblekatmandu.pl/stol-rozkladany-prostokatny-120cm-debowy-orbetello',
        ]);

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'sku_ordered' => $product4->sku,
            'name_ordered' => $product4->name,
            'quantity_ordered' => 2,
            'quantity_shipped' => 0,
            'price' => 649
        ]);

        $order->update(['total_paid' => $order->orderProducts()->sum('total_price')]);

    }
}
