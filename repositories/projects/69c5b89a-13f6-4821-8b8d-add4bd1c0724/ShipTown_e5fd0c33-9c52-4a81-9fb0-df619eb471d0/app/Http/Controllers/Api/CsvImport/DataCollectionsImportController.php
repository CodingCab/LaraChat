<?php

namespace App\Http\Controllers\Api\CsvImport;

use App\Http\Controllers\Controller;
use App\Models\DataCollection;
use App\Models\DataCollectionTransferIn;
use App\Models\DataCollectionTransferOut;
use App\Models\Warehouse;
use App\Modules\DataCollectorTransfersOutReserveStock\src\Jobs\CheckReservationsDataIntegrityJob;
use App\Modules\DataCollectorTransfersOutReserveStock\src\Jobs\CreateInventoryReservationsForExistingTransfersOutJob;
use App\Modules\Inventory\src\Jobs\RecalculateInventoryRecordsJob;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DataCollectionsImportController extends Controller
{
    /**
     * @var string[][]
     */
    private array $rules = [
        'data_collection_name_prefix' => ['required', 'string', 'max:255'],
        'data' => ['required', 'array'],
        'data.*.product_id' => ['required_if:product_sku,null', 'integer', 'exists:products,id'],
        'data.*.product_sku' => ['required_if:product_id,null', 'string', 'exists:products_aliases,alias'],
    ];

    public function store(Request $request): JsonResource
    {
        $finalRules = $this->rules;

        $warehouses = Warehouse::all();

        $warehouses->each(function ($warehouse) use (&$finalRules) {
            $finalRules['data.*.'.$warehouse->code] = ['sometimes', 'numeric', 'nullable'];
        });

        $validatedData = Validator::make($request->all(), $finalRules)->validate();

//        DB::transaction(function () use ($request, $warehouses, $validatedData) {
        $tempTableName = 'temp_csv_import_'.rand(100000000000000000, 999999999999999999);

        Schema::create($tempTableName, function (Blueprint $table) use ($warehouses) {
            $table->temporary();
            $table->id();
            $table->foreignId('product_id')->nullable();
            $table->string('product_sku')->nullable();

            $warehouses->each(function ($warehouse) use ($table) {
                $table->double($warehouse->code)->nullable();
            });

            $table->timestamps();
        });

        DB::table($tempTableName)->insert(data_get($validatedData, 'data', []));

        DB::statement('
            UPDATE '.$tempTableName.'
            LEFT JOIN products_aliases ON '.$tempTableName.'.product_sku = products_aliases.alias
            SET '.$tempTableName.'.product_id = products_aliases.product_id
            WHERE '.$tempTableName.'.product_id IS NULL
        ');

        $skuNotFoundErrors = DB::table($tempTableName)
            ->whereNull('product_id')
            ->select('product_sku')
            ->get()
            ->map(function ($item) use (&$errors) {
                return 'SKU not found: '.$item->product_sku;
            })
            ->filter();

        if ($skuNotFoundErrors->isNotEmpty()) {
            throw ValidationException::withMessages($skuNotFoundErrors->toArray());
        }

        $warehouses->each(function (Warehouse $warehouse) use ($tempTableName, $request) {
            $transferInRecords = DB::table($tempTableName)
                ->whereNotNull($warehouse->code)
                ->where($warehouse->code, '>', 0);

            $transferOutRecords = DB::table($tempTableName)
                ->whereNotNull($warehouse->code)
                ->where($warehouse->code, '<', 0);

            if ($transferInRecords->exists()) {
                $this->createTransferIn($warehouse, $request, $tempTableName);
            }

            if ($transferOutRecords->exists()) {
                $this->createTransferOut($warehouse, $request, $tempTableName);
            }

            DB::table('inventory')
                ->where('warehouse_id', $warehouse->id)
                ->whereIn('product_id', DB::table($tempTableName)->pluck('product_id'))
                ->update(['recount_required' => 1]);
        });
//        });

        RecalculateInventoryRecordsJob::dispatch();

        return JsonResource::make(['success' => true]);
    }

    private function createTransferIn(Warehouse $warehouse, Request $request, string $tempTableName): void
    {
        $dataCollector = DataCollection::query()->create([
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'name' => implode(' ', [$request->get('data_collection_name_prefix'), $warehouse->code]),
            'type' => DataCollectionTransferIn::class,
        ]);

        DB::statement('
            INSERT INTO data_collection_records (
                data_collection_id,
                inventory_id,
                warehouse_id,
                warehouse_code,
                product_id,
                quantity_requested,
                unit_cost,
                unit_full_price,
                unit_sold_price,
                created_at,
                updated_at
            )
            SELECT
                ' . $dataCollector->getKey() . ',
                inventory.id,
                inventory.warehouse_id,
                inventory.warehouse_code,
                inventory.product_id,
                IFNULL(`' . $warehouse->code . '`, 0) as quantity_requested,
                IFNULL(products_prices.cost, 0) as unit_cost,
                IFNULL(products_prices.price, 0) as unit_full_price,
                IFNULL(products_prices.price, 0) as unit_sold_price,
                NOW(),
                NOW()

            FROM ' . $tempTableName . '
            LEFT JOIN inventory
              ON ' . $tempTableName . '.product_id = inventory.product_id
              AND inventory.warehouse_id = ' . $warehouse->id . '
            LEFT JOIN products_prices
              ON inventory.id = products_prices.inventory_id

            WHERE IFNULL(`' . $warehouse->code . '`, 0) != 0
        ');

        CheckReservationsDataIntegrityJob::dispatch($dataCollector->getKey());
    }

    private function createTransferOut(Warehouse $warehouse, Request $request, string $tempTableName): void
    {
        $dataCollector = DataCollection::query()->create([
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'name' => implode(' ', [$request->get('data_collection_name_prefix'), $warehouse->code]),
            'type' => DataCollectionTransferOut::class,
        ]);

        DB::statement('
                INSERT INTO data_collection_records (
                    data_collection_id,
                    inventory_id,
                    warehouse_id,
                    warehouse_code,
                    product_id,
                    quantity_requested,
                    unit_cost,
                    unit_full_price,
                    unit_sold_price,
                    created_at,
                    updated_at
                )
                SELECT
                    ' . $dataCollector->getKey() . ',
                    inventory.id,
                    inventory.warehouse_id,
                    inventory.warehouse_code,
                    inventory.product_id,
                    (IFNULL(`' . $warehouse->code . '`, 0) * -1) as quantity_requested,
                    IFNULL(products_prices.cost, 0) as unit_cost,
                    IFNULL(products_prices.price, 0) as unit_full_price,
                    IFNULL(products_prices.price, 0) as unit_sold_price,
                    NOW(),
                    NOW()

                FROM ' . $tempTableName . '
                LEFT JOIN inventory
                  ON ' . $tempTableName . '.product_id = inventory.product_id
                  AND inventory.warehouse_id = ' . $warehouse->id . '
                LEFT JOIN products_prices
                  ON inventory.id = products_prices.inventory_id

                WHERE IFNULL(`' . $warehouse->code . '`, 0) != 0
            ');

        CheckReservationsDataIntegrityJob::dispatch($dataCollector->getKey());
    }
}
