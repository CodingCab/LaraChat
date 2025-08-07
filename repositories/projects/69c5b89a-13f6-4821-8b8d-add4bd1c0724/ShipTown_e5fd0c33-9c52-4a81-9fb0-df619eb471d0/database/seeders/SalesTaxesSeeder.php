<?php

namespace Database\Seeders;

use App\Modules\SalesTaxes\src\Models\SaleTax;
use Illuminate\Database\Seeder;

class SalesTaxesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salesTaxes = [
            [
                'code' => 'VAT_0',
                'rate' => 0,
            ],
            [
                'code' => 'VAT_5',
                'rate' => 5,
            ],
            [
                'code' => 'VAT_8',
                'rate' => 8,
            ],
            [
                'code' => 'VAT_23',
                'rate' => 23,
            ],
        ];

        foreach ($salesTaxes as $salesTax) {
            SaleTax::firstOrCreate($salesTax);
        }
    }
}
