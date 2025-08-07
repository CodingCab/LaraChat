<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use App\Modules\CsvProductImports\src\Jobs\AddWarehouseColumnsToCsvProductImportTableJob;
use App\Modules\CsvProductImports\src\Models\CsvProductImport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CsvProductImportsSeeder extends Seeder
{
    public function run(): void
    {
        AddWarehouseColumnsToCsvProductImportTableJob::dispatchSync();

        // Create or get a file record for this seeder
        $fileId = DB::table('modules_csv_uploaded_files')
            ->where('filename', 'seeder_import.csv')
            ->value('id');

        if (!$fileId) {
            $fileId = DB::table('modules_csv_uploaded_files')->insertGetId([
                'filename' => 'seeder_import.csv',
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $warehouses = Warehouse::all();

        $csvImports = [
            [
                'sku' => '45',
                'name' => 'Test product',
                'department' => 'General',
                'category' => 'Test',
                'weight' => 1.5,
                'length' => 10,
                'height' => 5,
                'width' => 8,
                'pack_quantity' => 1,
                'alias' => '45-alias',
                'tags_add' => 'test,demo',
                'tags_remove' => '',
                'price' => 19.99,
                'sale_price' => 14.99,
                'sale_price_start_date' => now()->startOfDay(),
                'sale_price_end_date' => now()->addDays(30)->endOfDay(),
                'commodity_code' => 'TEST001',
                'sales_tax_code' => 'STANDARD',
                'supplier' => 'ShipTown',
                'supplier_code' => 'ST-45',
            ],
            [
                'sku' => 'X001LE6Q8H',
                'name' => 'Eyoyo Clip On Barcode Scanner',
                'department' => 'Electronics',
                'category' => 'Scanners',
                'weight' => 0.250,
                'length' => 15,
                'height' => 8,
                'width' => 10,
                'pack_quantity' => 1,
                'alias' => 'X001LE6Q8H-alias',
                'tags_add' => 'electronics,scanner,barcode',
                'tags_remove' => '',
                'price' => 89.99,
                'sale_price' => null,
                'sale_price_start_date' => null,
                'sale_price_end_date' => null,
                'commodity_code' => 'ELEC001',
                'sales_tax_code' => 'STANDARD',
                'supplier' => 'Eyoyo',
                'supplier_code' => 'EY-X001',
            ],
            [
                'sku' => '40011',
                'name' => 'Secret Box',
                'department' => 'Gifts',
                'category' => 'Mystery',
                'weight' => 0.500,
                'length' => 20,
                'height' => 15,
                'width' => 15,
                'pack_quantity' => 1,
                'alias' => '40011-box',
                'tags_add' => 'gift,mystery',
                'tags_remove' => '',
                'price' => 29.99,
                'sale_price' => 24.99,
                'sale_price_start_date' => now()->startOfDay(),
                'sale_price_end_date' => now()->addDays(7)->endOfDay(),
                'commodity_code' => 'GIFT001',
                'sales_tax_code' => 'STANDARD',
                'supplier' => 'Mystery Makers',
                'supplier_code' => 'MM-40011',
            ],
            [
                'sku' => '40012',
                'name' => 'Power Adaptor',
                'department' => 'Electronics',
                'category' => 'Accessories',
                'weight' => 0.300,
                'length' => 12,
                'height' => 6,
                'width' => 8,
                'pack_quantity' => 1,
                'alias' => '40012-adaptor',
                'tags_add' => 'electronics,power,accessories',
                'tags_remove' => '',
                'price' => 15.99,
                'sale_price' => null,
                'sale_price_start_date' => null,
                'sale_price_end_date' => null,
                'commodity_code' => 'ELEC002',
                'sales_tax_code' => 'STANDARD',
                'supplier' => 'Power Solutions',
                'supplier_code' => 'PS-40012',
            ],
            [
                'sku' => '40013',
                'name' => 'Christmas Snowball',
                'department' => 'Seasonal',
                'category' => 'Christmas',
                'weight' => 1.200,
                'length' => 18,
                'height' => 18,
                'width' => 18,
                'pack_quantity' => 1,
                'alias' => '40013-snowball',
                'tags_add' => 'christmas,seasonal,decoration',
                'tags_remove' => '',
                'price' => 34.99,
                'sale_price' => 27.99,
                'sale_price_start_date' => now()->startOfMonth(),
                'sale_price_end_date' => now()->endOfMonth(),
                'commodity_code' => 'SEAS001',
                'sales_tax_code' => 'STANDARD',
                'supplier' => 'Holiday Decorations',
                'supplier_code' => 'HD-40013',
            ],
            [
                'sku' => '40014',
                'name' => 'Gloves Size L',
                'department' => 'Clothing',
                'category' => 'Winter Wear',
                'weight' => 0.150,
                'length' => 25,
                'height' => 5,
                'width' => 15,
                'pack_quantity' => 1,
                'alias' => '40014-gloves-L',
                'tags_add' => 'clothing,winter,accessories',
                'tags_remove' => '',
                'price' => 12.99,
                'sale_price' => null,
                'sale_price_start_date' => null,
                'sale_price_end_date' => null,
                'commodity_code' => 'CLOTH001',
                'sales_tax_code' => 'CLOTHING',
                'supplier' => 'Winter Wear Co',
                'supplier_code' => 'WW-40014',
            ],
            [
                'sku' => '40015',
                'name' => 'Buttons 100pk',
                'department' => 'Crafts',
                'category' => 'Sewing',
                'weight' => 0.200,
                'length' => 10,
                'height' => 3,
                'width' => 10,
                'pack_quantity' => 100,
                'alias' => '40015-buttons',
                'tags_add' => 'crafts,sewing,buttons',
                'tags_remove' => '',
                'price' => 8.99,
                'sale_price' => 6.99,
                'sale_price_start_date' => now()->startOfDay(),
                'sale_price_end_date' => now()->addDays(14)->endOfDay(),
                'commodity_code' => 'CRAFT001',
                'sales_tax_code' => 'STANDARD',
                'supplier' => 'Craft Supplies Ltd',
                'supplier_code' => 'CS-40015',
            ],
        ];

        foreach ($csvImports as $importData) {
            $csvImport = $importData;
            $csvImport['file_id'] = $fileId;

            // Add warehouse-specific data
            foreach ($warehouses as $warehouse) {
                $code = $warehouse->code;
                $csvImport["price_$code"] = $importData['price'];
                $csvImport["sale_price_$code"] = $importData['sale_price'];
                $csvImport["sale_price_start_date_$code"] = $importData['sale_price_start_date'];
                $csvImport["sale_price_end_date_$code"] = $importData['sale_price_end_date'];
                $csvImport["restock_level_$code"] = rand(20, 50);
                $csvImport["reorder_point_$code"] = rand(5, 15);
                $csvImport["shelve_location_$code"] = $this->generateShelveLocation();
            }

            CsvProductImport::create($csvImport);
        }

        // Create additional T-Shirt imports
        $tshirtData = [
            ['sku' => '4001', 'name' => 'T-Shirt Blue', 'color' => 'Blue'],
            ['sku' => '4002', 'name' => 'T-Shirt Brown Grey', 'color' => 'Brown Grey'],
            ['sku' => '4003', 'name' => 'T-Shirt Light Brown', 'color' => 'Light Brown'],
            ['sku' => '4004', 'name' => 'T-Shirt Light Grey', 'color' => 'Light Grey'],
            ['sku' => '4005', 'name' => 'T-Shirt Grey', 'color' => 'Grey'],
            ['sku' => '4006', 'name' => 'T-Shirt Black', 'color' => 'Black'],
            ['sku' => '4007', 'name' => 'T-Shirt Purple', 'color' => 'Purple'],
            ['sku' => '4008', 'name' => 'T-Shirt Green', 'color' => 'Green'],
            ['sku' => '4009', 'name' => 'T-Shirt White', 'color' => 'White'],
        ];

        foreach ($tshirtData as $tshirt) {
            $csvImport = [
                'file_id' => $fileId,
                'sku' => $tshirt['sku'],
                'name' => $tshirt['name'],
                'department' => 'Clothing',
                'category' => 'T-Shirts',
                'weight' => 0.200,
                'length' => 30,
                'height' => 2,
                'width' => 25,
                'pack_quantity' => 1,
                'alias' => $tshirt['sku'] . '-tshirt',
                'tags_add' => 'clothing,tshirt,' . strtolower(str_replace(' ', '-', $tshirt['color'])),
                'tags_remove' => '',
                'price' => 19.99,
                'sale_price' => null,
                'sale_price_start_date' => null,
                'sale_price_end_date' => null,
                'commodity_code' => 'TSHIRT001',
                'sales_tax_code' => 'CLOTHING',
                'supplier' => 'Fashion Basics',
                'supplier_code' => 'FB-' . $tshirt['sku'],
            ];

            // Add warehouse-specific data
            foreach ($warehouses as $warehouse) {
                $code = $warehouse->code;
                $csvImport["price_$code"] = 19.99;
                $csvImport["sale_price_$code"] = null;
                $csvImport["sale_price_start_date_$code"] = null;
                $csvImport["sale_price_end_date_$code"] = null;
                $csvImport["restock_level_$code"] = rand(30, 60);
                $csvImport["reorder_point_$code"] = rand(10, 20);
                $csvImport["shelve_location_$code"] = $this->generateShelveLocation();
            }

            CsvProductImport::create($csvImport);
        }
    }

    private function generateShelveLocation(): string
    {
        $aisle = chr(rand(65, 72)); // A-H
        $shelf = rand(1, 5);
        $position = rand(1, 20);

        return sprintf('%s%d-%d', $aisle, $shelf, $position);
    }
}
