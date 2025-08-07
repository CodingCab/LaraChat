<?php

namespace Database\Factories\Modules\CsvProductImports\src\Models;

use App\Modules\CsvProductImports\src\Models\CsvProductImport;
use App\Modules\CsvProductImports\src\Models\CsvUploadedFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class CsvProductImportFactory extends Factory
{
    protected $model = CsvProductImport::class;

    public function definition(): array
    {
        return [
            'file_id' => function () {
                return CsvUploadedFile::factory()->create()->id;
            },
            'sku' => $this->faker->unique()->ean8(),
            'name' => $this->faker->words(3, true),
            'department' => $this->faker->word,
            'category' => $this->faker->word,
            'weight' => $this->faker->randomFloat(3, 0.1, 10),
            'length' => $this->faker->randomFloat(3, 1, 100),
            'height' => $this->faker->randomFloat(3, 1, 100),
            'width' => $this->faker->randomFloat(3, 1, 100),
            'pack_quantity' => $this->faker->numberBetween(1, 10),
            'alias' => $this->faker->unique()->word,
            'tags_add' => implode(',', $this->faker->words(3)),
            'tags_remove' => null,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'sale_price' => $this->faker->randomFloat(2, 5, 900),
            'sale_price_start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'sale_price_end_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'commodity_code' => $this->faker->numerify('##########'),
            'sales_tax_code' => $this->faker->word,
            'supplier' => $this->faker->company,
            'supplier_code' => $this->faker->bothify('SUP-###'),
            'processed_at' => null,
        ];
    }
}
