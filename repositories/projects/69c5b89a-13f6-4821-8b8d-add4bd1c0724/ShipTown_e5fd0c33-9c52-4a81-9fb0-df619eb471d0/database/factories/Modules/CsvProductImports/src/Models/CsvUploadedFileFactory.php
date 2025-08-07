<?php

namespace Database\Factories\Modules\CsvProductImports\src\Models;

use App\Modules\CsvProductImports\src\Models\CsvUploadedFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class CsvUploadedFileFactory extends Factory
{
    protected $model = CsvUploadedFile::class;

    public function definition(): array
    {
        return [
            'filename' => $this->faker->word . '.csv',
            'mapped_fields' => [
                'sku' => 0,
                'name' => 1,
            ],
            'processed_at' => null,
        ];
    }
}