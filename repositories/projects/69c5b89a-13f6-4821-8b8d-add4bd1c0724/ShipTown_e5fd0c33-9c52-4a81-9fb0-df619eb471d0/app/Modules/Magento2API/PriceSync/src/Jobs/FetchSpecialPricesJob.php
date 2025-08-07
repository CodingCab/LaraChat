<?php

namespace App\Modules\Magento2API\PriceSync\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Helpers\TemporaryTable;
use App\Modules\Magento2API\InventorySync\src\Api\MagentoApi;
use App\Modules\Magento2API\PriceSync\src\Models\MagentoConnection;
use App\Modules\Magento2API\PriceSync\src\Models\PriceInformation;
use DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

/**
 * Class SyncCheckFailedProductsJob.
 */
class FetchSpecialPricesJob extends UniqueJob
{
    public function handle(): void
    {
        MagentoConnection::query()
            ->get()
            ->each(function (MagentoConnection $magentoConnection) {
                PriceInformation::query()
                    ->with(['product'])
                    ->where([
                        'connection_id' => $magentoConnection->getKey(),
                        'exists_in_magento' => true,
                        'special_prices_fetched_at' => null,
                    ])
                    ->chunkById(100, function (Collection $chunk) use ($magentoConnection) {
                        $productSkus = $chunk->map(function (PriceInformation $product) {
                            return $product->product->sku;
                        });

                        $response = MagentoApi::fetchSpecialPricesBulk($magentoConnection->api_access_token, $magentoConnection->base_url, $productSkus->toArray());

                        $responseRecords = collect($response->json())
                            ->filter(function ($apiBasePriceRecord) use ($magentoConnection) {
                                return $apiBasePriceRecord['store_id'] == $magentoConnection->magento_store_id;
                            })
                            ->map(function ($apiBasePriceRecord) use ($magentoConnection) {
                                return [
                                    'connection_id' => $magentoConnection->getKey(),
                                    'sku' => $apiBasePriceRecord['sku'],
                                    'magento_sale_price' => $apiBasePriceRecord['price'],
                                    'magento_sale_price_start_date' => $apiBasePriceRecord['price_from'],
                                    'magento_sale_price_end_date' => $apiBasePriceRecord['price_to'],
                                    'special_prices_fetched_at' => now(),
                                    'special_prices_raw_import' => json_encode($apiBasePriceRecord),
                                ];
                            });

                        TemporaryTable::fromArray('tempTable_MagentoSpecialPriceFetch', $responseRecords->toArray(), function (Blueprint $table) {
                            $table->temporary();
                            $table->unsignedBigInteger('connection_id')->index();
                            $table->string('sku')->index();
                            $table->decimal('magento_sale_price', 12, 4)->nullable();
                            $table->dateTime('magento_sale_price_start_date')->nullable();
                            $table->dateTime('magento_sale_price_end_date')->nullable();
                            $table->dateTime('special_prices_fetched_at')->nullable();
                            $table->json('special_prices_raw_import')->nullable();
                        });

                        DB::statement('
                            UPDATE modules_magento2api_products
                            INNER JOIN tempTable_MagentoSpecialPriceFetch
                                ON tempTable_MagentoSpecialPriceFetch.sku = modules_magento2api_products.sku
                                AND tempTable_MagentoSpecialPriceFetch.connection_id = modules_magento2api_products.connection_id
                            SET modules_magento2api_products.special_price_sync_required = null,
                                modules_magento2api_products.magento_sale_price = tempTable_MagentoSpecialPriceFetch.magento_sale_price,
                                modules_magento2api_products.magento_sale_price_start_date = tempTable_MagentoSpecialPriceFetch.magento_sale_price_start_date,
                                modules_magento2api_products.magento_sale_price_end_date = tempTable_MagentoSpecialPriceFetch.magento_sale_price_end_date,
                                modules_magento2api_products.special_prices_fetched_at = tempTable_MagentoSpecialPriceFetch.special_prices_fetched_at,
                                modules_magento2api_products.special_prices_raw_import = tempTable_MagentoSpecialPriceFetch.special_prices_raw_import
                        ');

                        // Update missing records
                        PriceInformation::query()
                            ->whereIn('id', $chunk->pluck('id'))
                            ->whereNotIn('sku', $responseRecords->pluck('sku'))
                            ->update([
                                'special_prices_fetched_at' => now(),
                                'special_prices_raw_import' => null,
                                'magento_sale_price' => null,
                                'magento_sale_price_start_date' => null,
                                'magento_sale_price_end_date' => null,
                            ]);

                        usleep(500000); // 0.5 seconds
                    });
            });
    }
}
