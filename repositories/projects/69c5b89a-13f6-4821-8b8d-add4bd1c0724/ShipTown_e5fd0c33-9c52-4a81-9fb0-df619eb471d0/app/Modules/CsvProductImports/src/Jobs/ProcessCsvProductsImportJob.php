<?php

namespace App\Modules\CsvProductImports\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\CsvProductImports\src\Models\CsvProductImport;
use App\Modules\CsvProductImports\src\Models\CsvUploadedFile;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProcessCsvProductsImportJob extends UniqueJob
{
    protected int $uploadedFileId;
    protected array $mappedFields = [];

    public function __construct(int $uploadedFileId = 0)
    {
        $this->uploadedFileId = $uploadedFileId;
    }

    public function uniqueId(): string
    {
        return implode('-', [get_class($this), $this->uploadedFileId]);
    }

    /**
     * @throws Exception
     */
    public function handle(): bool
    {
        if ($this->uploadedFileId <= 0) {
            CsvUploadedFile::query()
                ->whereNull('processed_at')
                ->chunkById(1, function (Collection $collection) {
                    $collection->each(function (CsvUploadedFile $csvUploadedFile) {
                        ProcessCsvProductsImportJob::dispatch($csvUploadedFile->id);
                    });
                });

            return true;
        }

        $this->processRecords($this->uploadedFileId);

        return true;
    }

    public function processRecords($fileId): bool
    {
        $uploadedFile = CsvUploadedFile::query()->find($fileId);

        if ($uploadedFile && $uploadedFile->mapped_fields) {
            $this->mappedFields = array_keys($uploadedFile->mapped_fields);
        }

        $this->importProducts(); // product_exists
        $this->importProductAttributes(); // product_updated
        $this->importProductTags(); // tags_added, tags_removed
        $this->importProductAliases(); // aliases_imported
        $this->importWarehouseInventory(); // inventory_updated
        $this->importWarehousePricing(); // pricing_imported

        CsvProductImport::query()
            ->where('file_id', $fileId)
            ->where('product_exists', 1)

            ->whereNotNull('product_id')
            ->whereNull('processed_at')
            ->update(['processed_at' => now()]);

        return true;
    }

    public function fillProductIds(): void
    {
        CsvProductImport::query()
            ->select(['id'])
            ->whereNull('product_id')
            ->whereNull('processed_at')
            ->where(['file_id' => $this->uploadedFileId])
            ->chunkById(1000, function (Collection $collection) {
                $ids = $collection->pluck('id')->toArray();
                $placeholders = implode(',', array_fill(0, count($ids), '?'));

                DB::affectingStatement("
                        UPDATE modules_csv_product_imports
                        INNER JOIN products_aliases
                            ON products_aliases.alias = modules_csv_product_imports.sku
                        SET modules_csv_product_imports.product_id = products_aliases.product_id,
                            modules_csv_product_imports.product_exists =
                                1
                        WHERE modules_csv_product_imports.id IN ({$placeholders})
                    ", $ids);
            });
    }

    public function importProductAttributes(): void
    {
        CsvProductImport::query()
            ->whereNotNull('product_id')
            ->whereNull('processed_at')
            ->where(['file_id' => $this->uploadedFileId])
            ->chunkById(1000, function (Collection $collection) {
                $ids = $collection->pluck('id')->toArray();
                $placeholders = implode(',', array_fill(0, count($ids), '?'));

                // Update product attributes for mapped fields
                $updateFields = [];
                $fieldMapping = [
                    'name',
                    'department',
                    'category',
                    'price',
                    'sale_price',
                    'sale_price_start_date',
                    'sale_price_end_date',
                    'commodity_code',
                    'supplier',
                    'supplier_code',
                    'sales_tax_code',
                    'weight',
                    'length',
                    'width',
                    'height',
                    'pack_quantity',
                ];

                foreach ($fieldMapping as $csvField) {
                    if (in_array($csvField, $this->mappedFields)) {
                        $updateFields[] = "products.$csvField = CASE
                            WHEN modules_csv_product_imports.$csvField IS NOT NULL
                            THEN modules_csv_product_imports.$csvField
                            ELSE products.$csvField
                        END";
                    }
                }

                if (!empty($updateFields)) {
                    $updateFieldsStr = implode(', ', $updateFields);

                    DB::affectingStatement("
                        UPDATE products
                        INNER JOIN modules_csv_product_imports
                            ON products.id = modules_csv_product_imports.product_id
                        SET {$updateFieldsStr},
                            products.updated_at = NOW()
                        WHERE modules_csv_product_imports.id IN ({$placeholders})
                    ", $ids);
                }

                // Mark records as having product updated
                DB::affectingStatement("
                    UPDATE modules_csv_product_imports
                    SET product_updated = 1
                    WHERE id IN ({$placeholders})
                ", $ids);
            });
    }

    public function importProductTags(): void
    {
        // Process tags_add
        if (in_array('tags_add', $this->mappedFields)) {
            CsvProductImport::query()
                ->whereNotNull('product_id')
                ->whereNotNull('tags_add')
                ->where('tags_add', '!=', '')
                ->whereNull('tags_added')
                ->whereNull('processed_at')
                ->where(['file_id' => $this->uploadedFileId])
                ->chunkById(100, function (Collection $collection) {
                    foreach ($collection as $record) {
                        $product = Product::find($record->product_id);
                        $tags = array_filter(array_map('trim', explode(',', $record->tags_add)));
                        foreach ($tags as $tag) {
                            $product->attachTag($tag);
                        }

                        CsvProductImport::where('id', $record->id)
                            ->update(['tags_added' => 1]);
                    }
                });
        }

        // Process tags_remove
        if (in_array('tags_remove', $this->mappedFields)) {
            CsvProductImport::query()
                ->whereNotNull('product_id')
                ->whereNotNull('tags_remove')
                ->where('tags_remove', '!=', '')
                ->whereNull('tags_removed')
                ->whereNull('processed_at')
                ->where(['file_id' => $this->uploadedFileId])
                ->chunkById(100, function (Collection $collection) {
                    foreach ($collection as $record) {
                        $product = Product::find($record->product_id);
                        if ($product) {
                            $tags = array_filter(array_map('trim', explode(',', $record->tags_remove)));
                            if (!empty($tags)) {
                                $product->detachTags($tags);
                            }

                            CsvProductImport::where('id', $record->id)
                                ->update(['tags_removed' => 1]);
                        }
                    }
                });
        }
    }

    public function importProductAliases(): void
    {
        CsvProductImport::query()
            ->whereNotNull('product_id')
            ->whereNotNull('alias')
            ->where('alias', '!=', '')
            ->whereNull('aliases_imported')
            ->whereNull('processed_at')
            ->where(['file_id' => $this->uploadedFileId])
            ->chunkById(1000, function (Collection $collection) {
                $ids = $collection->pluck('id')->toArray();
                $placeholders = implode(',', array_fill(0, count($ids), '?'));

                // Insert aliases using INSERT IGNORE to handle duplicates
                if (in_array('alias', $this->mappedFields)) {
                    DB::affectingStatement("
                        INSERT IGNORE INTO products_aliases (product_id, alias, created_at, updated_at)
                        SELECT product_id, alias, NOW(), NOW()
                        FROM modules_csv_product_imports
                        WHERE id IN ({$placeholders})
                    ", $ids);
                }

                // Mark records as having aliases imported
                DB::affectingStatement("
                    UPDATE modules_csv_product_imports
                    SET aliases_imported = 1
                    WHERE id IN ({$placeholders})
                ", $ids);

                usleep(50000); // sleep for 0.05
            });
    }

    public function importWarehouseInventory(): void
    {
        $warehouses = Warehouse::query()->get()->keyBy('code');

        foreach ($warehouses as $warehouseCode => $warehouse) {
            // Check if any inventory fields were mapped for this warehouse
            $shelveLocationField = "shelve_location_$warehouseCode";
            $reorderPointField = "reorder_point_$warehouseCode";
            $restockLevelField = "restock_level_$warehouseCode";

            $hasMappedFields = in_array($shelveLocationField, $this->mappedFields) ||
                in_array($reorderPointField, $this->mappedFields) ||
                in_array($restockLevelField, $this->mappedFields);

            if (!$hasMappedFields) {
                continue;
            }

            CsvProductImport::query()
                ->whereNotNull('product_id')
                ->whereNull('inventory_updated')
                ->whereNull('processed_at')
                ->where(['file_id' => $this->uploadedFileId])
                ->where(function ($query) use ($shelveLocationField, $reorderPointField, $restockLevelField) {
                    $query->whereNotNull($shelveLocationField)
                        ->orWhereNotNull($reorderPointField)
                        ->orWhereNotNull($restockLevelField);
                })
                ->chunkById(1000, function (Collection $collection) use ($warehouse, $warehouseCode, $shelveLocationField, $reorderPointField, $restockLevelField) {
                    $ids = $collection->pluck('id')->toArray();
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));

                    // Insert inventory records if they don't exist
                    DB::affectingStatement("
                        INSERT IGNORE INTO inventory (product_id, warehouse_id, product_sku, warehouse_code, created_at, updated_at)
                        SELECT product_id, ?, sku, ?, NOW(), NOW()
                        FROM modules_csv_product_imports
                        WHERE id IN ({$placeholders})
                    ", array_merge([$warehouse->id, $warehouseCode], $ids));

                    // Build update statement for inventory fields
                    $updateFields = [];
                    if (in_array($shelveLocationField, $this->mappedFields)) {
                        $updateFields[] = "inventory.shelve_location = IFNULL(modules_csv_product_imports.$shelveLocationField, inventory.shelve_location)";
                    }
                    if (in_array($reorderPointField, $this->mappedFields)) {
                        $updateFields[] = "inventory.reorder_point = IFNULL(modules_csv_product_imports.$reorderPointField, inventory.reorder_point)";
                    }
                    if (in_array($restockLevelField, $this->mappedFields)) {
                        $updateFields[] = "inventory.restock_level = IFNULL(modules_csv_product_imports.$restockLevelField, inventory.restock_level)";
                    }

                    if (!empty($updateFields)) {
                        $updateFieldsStr = implode(', ', $updateFields);

                        DB::affectingStatement("
                            UPDATE inventory
                            INNER JOIN modules_csv_product_imports
                                ON inventory.product_id = modules_csv_product_imports.product_id
                                AND inventory.warehouse_id = ?
                            SET {$updateFieldsStr},
                                inventory.updated_at = NOW()
                            WHERE modules_csv_product_imports.id IN ({$placeholders})
                        ", array_merge([$warehouse->id], $ids));
                    }

                    // Mark records as having inventory updated
                    DB::affectingStatement("
                        UPDATE modules_csv_product_imports
                        SET inventory_updated = 1
                        WHERE id IN ({$placeholders})
                    ", $ids);
                });
        }
    }

    public function importWarehousePricing(): void
    {
        $warehouses = Warehouse::query()->get()->keyBy('code');

        foreach ($warehouses as $warehouseCode => $warehouse) {
            // Check if any pricing fields were mapped for this warehouse
            $priceField = "price_$warehouseCode";
            $salePriceField = "sale_price_$warehouseCode";
            $salePriceStartField = "sale_price_start_date_$warehouseCode";
            $salePriceEndField = "sale_price_end_date_$warehouseCode";

            $hasMappedFields = in_array($priceField, $this->mappedFields) ||
                in_array($salePriceField, $this->mappedFields) ||
                in_array($salePriceStartField, $this->mappedFields) ||
                in_array($salePriceEndField, $this->mappedFields);

            if (!$hasMappedFields) {
                continue;
            }

            CsvProductImport::query()
                ->whereNotNull('product_id')
                ->whereNull('pricing_imported')
                ->whereNull('processed_at')
                ->where(['file_id' => $this->uploadedFileId])
                ->where(function ($query) use ($priceField, $salePriceField, $salePriceStartField, $salePriceEndField) {
                    $query->whereNotNull($priceField)
                        ->orWhereNotNull($salePriceField)
                        ->orWhereNotNull($salePriceStartField)
                        ->orWhereNotNull($salePriceEndField);
                })
                ->chunkById(1000, function (Collection $collection) use ($warehouse, $warehouseCode, $priceField, $salePriceField, $salePriceStartField, $salePriceEndField) {
                    $ids = $collection->pluck('id')->toArray();
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));

                    // First ensure inventory records exist and get their IDs
                    DB::affectingStatement("
                        INSERT IGNORE INTO inventory (product_id, warehouse_id, product_sku, warehouse_code, created_at, updated_at)
                        SELECT product_id, ?, sku, ?, NOW(), NOW()
                        FROM modules_csv_product_imports
                        WHERE id IN ({$placeholders})
                    ", array_merge([$warehouse->id, $warehouseCode], $ids));

                    // Insert product prices if they don't exist
                    DB::affectingStatement("
                        INSERT IGNORE INTO products_prices (inventory_id, product_id, warehouse_id, warehouse_code, created_at, updated_at)
                        SELECT inventory.id, inventory.product_id, inventory.warehouse_id, inventory.warehouse_code, NOW(), NOW()
                        FROM inventory
                        INNER JOIN modules_csv_product_imports
                            ON inventory.product_id = modules_csv_product_imports.product_id
                            AND inventory.warehouse_id = ?
                        WHERE modules_csv_product_imports.id IN ({$placeholders})
                    ", array_merge([$warehouse->id], $ids));

                    // Build update statement for pricing fields
                    $updateFields = [];
                    if (in_array($priceField, $this->mappedFields)) {
                        $updateFields[] = "products_prices.price = IFNULL(modules_csv_product_imports.$priceField, products_prices.price)";
                    }
                    if (in_array($salePriceField, $this->mappedFields)) {
                        $updateFields[] = "products_prices.sale_price = modules_csv_product_imports.$salePriceField";
                    }
                    if (in_array($salePriceStartField, $this->mappedFields)) {
                        $updateFields[] = "products_prices.sale_price_start_date = modules_csv_product_imports.$salePriceStartField";
                    }
                    if (in_array($salePriceEndField, $this->mappedFields)) {
                        $updateFields[] = "products_prices.sale_price_end_date = modules_csv_product_imports.$salePriceEndField";
                    }

                    if (!empty($updateFields)) {
                        $updateFieldsStr = implode(', ', $updateFields);

                        DB::affectingStatement("
                            UPDATE products_prices
                            INNER JOIN inventory
                                ON products_prices.inventory_id = inventory.id
                            INNER JOIN modules_csv_product_imports
                                ON inventory.product_id = modules_csv_product_imports.product_id
                                AND inventory.warehouse_id = ?
                            SET {$updateFieldsStr},
                                products_prices.updated_at = NOW()
                            WHERE modules_csv_product_imports.id IN ({$placeholders})
                        ", array_merge([$warehouse->id], $ids));
                    }

                    // Mark records as having pricing imported
                    DB::affectingStatement("
                        UPDATE modules_csv_product_imports
                        SET pricing_imported = 1
                        WHERE id IN ({$placeholders})
                    ", $ids);
                });
        }
    }

    private function importProducts(): void
    {
        $this->fillProductIds(); // product_id

        CsvProductImport::query()
            ->where('file_id', $this->uploadedFileId)
            ->whereNull('processed_at')
            ->whereNull('product_id')
            ->whereNotNull('sku')
            ->whereNotNull('name')
            ->where('sku', '!=', '')
            ->where('name', '!=', '')
            ->chunkById(1000, function (Collection $collection) {
                $productData = [];
                $now = now();

                foreach ($collection as $record) {
                    $productData[] = [
                        'sku' => $record->sku,
                        'name' => $record->name ?: $record->sku,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($productData)) {
                    Product::query()->insert($productData);
                }

                // Mark records as having pricing imported
                CsvProductImport::query()
                    ->whereIn('id', $collection->pluck('id')->toArray())
                    ->update(['product_exists' => 1]);
            });

        $this->fillProductIds(); // fill product_id for newly created products
    }
}
