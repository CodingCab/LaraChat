<?php

namespace App\Modules\Rmsapi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Heartbeat;
use App\Modules\Rmsapi\src\Api\Client as RmsapiClient;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class ImportProductsJob extends UniqueJob
{
    private RmsapiConnection $rmsConnection;

    public string $batch_uuid;

    public function uniqueId(): string
    {
        return implode('-', [get_class($this), $this->rmsConnection->getKey()]);
    }

    public function __construct(int $rmsapiConnectionId)
    {
        $this->rmsConnection = RmsapiConnection::find($rmsapiConnectionId);
        $this->batch_uuid = Uuid::uuid4()->toString();
    }

    public function handle(): bool
    {
        $per_page = 500;
        $roundsLeft = 20;

        do {
            $this->rmsConnection->refresh();

            $params = [
                'per_page' => $per_page,
                'order_by' => 'db_change_stamp:asc',
                'min:db_change_stamp' => $this->rmsConnection->products_last_timestamp,
            ];

            try {
                $response = RmsapiClient::GET($this->rmsConnection, 'api/products', $params);
            } catch (GuzzleException $e) {
                report($e);

                Log::warning('RMSAPI ImportProductsJob Failed product fetch', [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ]);

                return false;
            }

            if ($response->getResult()) {
                $this->saveImportedProducts($response->getResult());
            }

            Log::info('Job processing', [
                'job' => self::class,
                'connection_id' => $this->rmsConnection->getKey(),
                'warehouse_code' => $this->rmsConnection->location_id,
                'records_downloaded' => count($response->getResult()),
                'records_to_download' => $response->asArray()['total'] - count($response->getResult()),
            ]);

            $roundsLeft--;
        } while ((isset($response->asArray()['next_page_url'])) && ($roundsLeft > 0));

        Heartbeat::query()->updateOrCreate([
            'code' => 'models_rmsapi_successful_fetch_warehouseId_'.$this->rmsConnection->location_id,
        ], [
            'error_message' => 'RMSAPI not synced for last hour WarehouseID: '.$this->rmsConnection->location_id,
            'expires_at' => now()->addHour(),
        ]);

        return true;
    }

    /**
     * @throws Exception
     */
    public function saveImportedProducts(array $productList)
    {
        // we will use the same time for all records to speed up process
        $time = now()->toDateTimeString();

        $productsCollection = collect($productList);

        $insertData = $productsCollection->map(function ($product) {
            return [
                'connection_id' => $this->rmsConnection->getKey(),
                'warehouse_id' => $this->rmsConnection->warehouse_id,
                'warehouse_code' => $this->rmsConnection->location_id,
                'batch_uuid' => $this->batch_uuid,
                'sku' => data_get($product, 'item_code'),
                'name' => data_get($product, 'description'),
                'rms_product_id' => data_get($product, 'id'),
                'quantity_on_hand' => data_get($product, 'quantity_on_hand', 0),
                'quantity_on_order' => data_get($product, 'quantity_on_order', 0),
                'quantity_available' => data_get($product, 'quantity_available', 0),
                'quantity_committed' => data_get($product, 'quantity_committed', 0),
                'reorder_point' => data_get($product, 'reorder_point', 0),
                'restock_level' => data_get($product, 'restock_level', 0),
                'price' => data_get($product, 'price', 0),
                'price_a' => data_get($product, 'price_a', 0),
                'cost' => data_get($product, 'cost', 0),
                'sale_price' => data_get($product, 'sale_price', 0),
                'sale_price_start_date' => data_get($product, 'sale_price_start_date', '2000-01-01'),
                'sale_price_end_date' => data_get($product, 'sale_price_end_date', '2000-01-01'),
                'is_web_item' => data_get($product, 'is_web_item', false),
                'department_name' => data_get($product, 'department_name', ''),
                'category_name' => data_get($product, 'category_name', ''),
                'supplier_name' => data_get($product, 'supplier_name', ''),
                'sub_description_1' => data_get($product, 'sub_description_1', ''),
                'sub_description_3' => data_get($product, 'sub_description_3', ''),
                'sub_description_2' => data_get($product, 'sub_description_2', ''),
                'raw_import' => json_encode($product),
            ];
        });

        DB::statement('DROP TEMPORARY TABLE IF EXISTS tempTable;');

        DB::statement('
            CREATE TEMPORARY TABLE tempTable
            (
                `connection_id`         bigint unsigned DEFAULT NULL,
                `warehouse_id`          bigint unsigned DEFAULT NULL,
                `warehouse_code`        varchar(5) DEFAULT NULL,
                `batch_uuid`            char(36) DEFAULT NULL,
                `sku`                   varchar(255) DEFAULT NULL,
                `name`                  varchar(255) DEFAULT NULL,
                `rms_product_id`        bigint unsigned DEFAULT NULL,
                `quantity_on_hand`      decimal(20, 2) DEFAULT NULL,
                `quantity_on_order`     decimal(20, 2) DEFAULT NULL,
                `quantity_available`    decimal(20, 2) DEFAULT NULL,
                `quantity_committed`    decimal(20, 2) DEFAULT NULL,
                `reorder_point`         decimal(20, 2)  DEFAULT NULL,
                `restock_level`         decimal(20, 2)  DEFAULT NULL,
                `price`                 decimal(20, 2)  DEFAULT NULL,
                `price_a`               decimal(20, 2)  DEFAULT NULL,
                `cost`                  decimal(20, 2)  DEFAULT NULL,
                `sale_price`            decimal(20, 2)  DEFAULT NULL,
                `sale_price_start_date` timestamp DEFAULT NULL,
                `sale_price_end_date`   timestamp DEFAULT NULL,
                `is_web_item`           tinyint(1) DEFAULT NULL,
                `department_name`       varchar(255) DEFAULT NULL,
                `category_name`         varchar(255) DEFAULT NULL,
                `supplier_name`         varchar(255) DEFAULT NULL,
                `sub_description_1`     varchar(255) DEFAULT NULL,
                `sub_description_2`     varchar(255) DEFAULT NULL,
                `sub_description_3`     varchar(255) DEFAULT NULL,
                `raw_import`            json DEFAULT NULL
            ) COLLATE = utf8mb4_unicode_ci
        ');
        // we will use insert instead of create as this is way faster
        // method of inputting bulk of records to database
        // this won't invoke any events
        DB::table('tempTable')->insert($insertData->toArray());

        DB::statement('
            UPDATE modules_rmsapi_products_imports
            INNER JOIN tempTable
                ON modules_rmsapi_products_imports.rms_product_id = tempTable.rms_product_id
                AND modules_rmsapi_products_imports.connection_id = tempTable.connection_id
            SET
                modules_rmsapi_products_imports.processed_at = null,
                modules_rmsapi_products_imports.reserved_at = null,
                modules_rmsapi_products_imports.updated_at = now(),
                modules_rmsapi_products_imports.sku = tempTable.sku,
                modules_rmsapi_products_imports.name = tempTable.name,
                modules_rmsapi_products_imports.batch_uuid = tempTable.batch_uuid,
                modules_rmsapi_products_imports.connection_id = tempTable.connection_id,
                modules_rmsapi_products_imports.warehouse_id = tempTable.warehouse_id,
                modules_rmsapi_products_imports.warehouse_code = tempTable.warehouse_code,
                modules_rmsapi_products_imports.reorder_point = tempTable.reorder_point,
                modules_rmsapi_products_imports.restock_level = tempTable.restock_level,
                modules_rmsapi_products_imports.price = tempTable.price,
                modules_rmsapi_products_imports.price_a = tempTable.price_a,
                modules_rmsapi_products_imports.cost = tempTable.cost,
                modules_rmsapi_products_imports.sale_price = tempTable.sale_price,
                modules_rmsapi_products_imports.sale_price_start_date = tempTable.sale_price_start_date,
                modules_rmsapi_products_imports.sale_price_end_date = tempTable.sale_price_end_date,
                modules_rmsapi_products_imports.quantity_on_hand = tempTable.quantity_on_hand,
                modules_rmsapi_products_imports.quantity_committed = tempTable.quantity_committed,
                modules_rmsapi_products_imports.quantity_available = tempTable.quantity_available,
                modules_rmsapi_products_imports.quantity_on_order = tempTable.quantity_on_order,
                modules_rmsapi_products_imports.is_web_item = tempTable.is_web_item,
                modules_rmsapi_products_imports.department_name = tempTable.department_name,
                modules_rmsapi_products_imports.category_name = tempTable.category_name,
                modules_rmsapi_products_imports.sub_description_1 = tempTable.sub_description_1,
                modules_rmsapi_products_imports.sub_description_2 = tempTable.sub_description_2,
                modules_rmsapi_products_imports.sub_description_3 = tempTable.sub_description_3,
                modules_rmsapi_products_imports.supplier_name = tempTable.supplier_name,
                modules_rmsapi_products_imports.raw_import = tempTable.raw_import
        ');

        DB::statement('
            INSERT INTO modules_rmsapi_products_imports (
                processed_at,
                reserved_at,
                updated_at,
                created_at,
                sku,
                name,
                rms_product_id,
                batch_uuid,
                connection_id,
                warehouse_id,
                warehouse_code,
                reorder_point,
                restock_level,
                price,
                price_a,
                cost,
                sale_price,
                sale_price_start_date,
                sale_price_end_date,
                quantity_on_hand,
                quantity_committed,
                quantity_available,
                quantity_on_order,
                is_web_item,
                department_name,
                category_name,
                sub_description_1,
                sub_description_2,
                sub_description_3,
                supplier_name,
                raw_import
            )
            SELECT
                null as processed_at,
                null as reserved_at,
                now() as updated_at,
                now() as created_at,
                tempTable.sku,
                tempTable.name,
                tempTable.rms_product_id,
                tempTable.batch_uuid,
                tempTable.connection_id,
                tempTable.warehouse_id,
                tempTable.warehouse_code,
                tempTable.reorder_point,
                tempTable.restock_level,
                tempTable.price,
                tempTable.price_a,
                tempTable.cost,
                tempTable.sale_price,
                tempTable.sale_price_start_date,
                tempTable.sale_price_end_date,
                tempTable.quantity_on_hand,
                tempTable.quantity_committed,
                tempTable.quantity_available,
                tempTable.quantity_on_order,
                tempTable.is_web_item,
                tempTable.department_name,
                tempTable.category_name,
                tempTable.sub_description_1,
                tempTable.sub_description_2,
                tempTable.sub_description_3,
                tempTable.supplier_name,
                tempTable.raw_import

            FROM tempTable

            LEFT JOIN modules_rmsapi_products_imports
                ON modules_rmsapi_products_imports.rms_product_id = tempTable.rms_product_id
                AND modules_rmsapi_products_imports.connection_id = tempTable.connection_id

            WHERE modules_rmsapi_products_imports.id IS NULL
        ');

        $this->rmsConnection->update(['products_last_timestamp' => $productsCollection->last()['db_change_stamp']]);
    }
}
