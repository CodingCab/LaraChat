<?php

namespace App\Modules\Magento2API\PriceSync\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Magento2API\InventorySync\src\Api\MagentoApi;
use App\Modules\Magento2API\PriceSync\src\Models\PriceInformation;
use Illuminate\Support\Collection;

/**
 * Class SyncCheckFailedProductsJob.
 */
class SyncProductBasePricesJob extends UniqueJob
{
    public function handle(): void
    {
        PriceInformation::query()
            ->with(['magentoConnection', 'product', 'prices'])
            ->where(['base_price_sync_required' => true])
            ->chunkById(10, function (Collection $chunk) {
                $chunk->each(function (PriceInformation $magentoProduct) {
                    MagentoApi::postProductsBasePrices(
                        $magentoProduct->magentoConnection,
                        $magentoProduct->product->sku,
                        $magentoProduct->prices->price,
                        $magentoProduct->magentoConnection->magento_store_id ?? 0
                    );

                    $magentoProduct->update([
                        'base_price_sync_required' => null,
                        'base_prices_fetched_at' => null,
                        'base_prices_raw_import' => null,
                        'magento_price' => null,
                    ]);
                });
            });
    }
}
