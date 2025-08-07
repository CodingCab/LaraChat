<?php

namespace App\Modules\Magento2API\PriceSync\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Magento2API\InventorySync\src\Api\MagentoApi;
use App\Modules\Magento2API\PriceSync\src\Models\MagentoConnection;
use App\Modules\Magento2API\PriceSync\src\Models\PriceInformation;
use Illuminate\Support\Collection;

/**
 * Class SyncCheckFailedProductsJob.
 */
class SyncProductSalePricesBulkJob extends UniqueJob
{
    public function handle(): void
    {
        MagentoConnection::query()
            ->each(function (MagentoConnection $magentoConnection) {
                PriceInformation::query()
                    ->with(['product', 'prices'])
                    ->where('connection_id', $magentoConnection->id)
                    ->where(['special_price_sync_required' => true])
                    ->chunkById(10, function (Collection $chunk) use ($magentoConnection) {
                        $specialPrices = $chunk->map(function (PriceInformation $magentoProduct) use ($magentoConnection) {
                            return [
                                'sku' => $magentoProduct->product->sku,
                                'store_id' => $magentoConnection->magento_store_id ?? 0,
                                'price' => $magentoProduct->prices->sale_price,
                                'price_from' => $magentoProduct->prices->sale_price_start_date->format('Y-m-d H:i:s'),
                                'price_to' => $magentoProduct->prices->sale_price_end_date->format('Y-m-d H:i:s'),
                            ];
                        });

                        MagentoApi::postProductsSpecialPriceArray(
                            $magentoConnection->base_url,
                            $magentoConnection->api_access_token,
                            $specialPrices->toArray()
                        );

                        PriceInformation::query()
                            ->whereIn('id', $chunk->pluck('id'))
                            ->update([
                                'special_price_sync_required' => null,
                                'magento_sale_price' => null,
                                'magento_sale_price_start_date' => null,
                                'magento_sale_price_end_date' => null,
                                'special_prices_fetched_at' => null,
                                'special_prices_raw_import' => null,
                            ]);
                    });
            });
    }
}
