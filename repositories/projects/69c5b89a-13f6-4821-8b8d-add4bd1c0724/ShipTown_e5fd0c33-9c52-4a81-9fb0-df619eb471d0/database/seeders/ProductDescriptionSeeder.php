<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductDescription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductDescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product = Product::first() ?? Product::factory()->create();
        $lang = ['en', 'de', 'es'];

        foreach($lang as $code) {
            ProductDescription::factory()->create([
                'product_id' => $product->id,
                'language_code' => $code
            ]);
        }


    }
}
