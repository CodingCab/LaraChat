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
class SyncProductBasePricesBulkJob extends UniqueJob
{
    public function handle(): void
    {
        MagentoConnection::query()
            ->each(function (MagentoConnection $magentoConnection) {
                PriceInformation::query()
                    ->with(['magentoConnection', 'product', 'prices'])
                    ->where(['base_price_sync_required' => true])
                    ->chunkById(10, function (Collection $chunk) use ($magentoConnection) {
                        $attributes = $chunk->map(function (PriceInformation $magentoProduct) {
                            return [
                                'sku' => $magentoProduct->product->sku,
                                'price' => $magentoProduct->prices->price,
                                'store_id' => $magentoProduct->magentoConnection->magento_store_id ?? 0,
                            ];
                        });

                        MagentoApi::postProductsBaseBulkPrices(
                            $magentoConnection->api_access_token,
                            $magentoConnection->base_url,
                            $attributes->toArray()
                        );

                        PriceInformation::query()
                            ->whereIn('id', $chunk->pluck('id'))
                            ->update([
                                'base_price_sync_required' => null,
                                'base_prices_fetched_at' => null,
                                'base_prices_raw_import' => null,
                                'magento_price' => null,
                            ]);
                    });
            });
    }
}
