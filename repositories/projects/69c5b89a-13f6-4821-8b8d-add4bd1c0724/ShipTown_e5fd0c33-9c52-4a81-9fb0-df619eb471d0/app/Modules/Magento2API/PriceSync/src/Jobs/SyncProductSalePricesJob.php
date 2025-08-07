<?php

namespace App\Modules\Magento2API\PriceSync\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Magento2API\InventorySync\src\Api\MagentoApi;
use App\Modules\Magento2API\PriceSync\src\Models\PriceInformation;

/**
 * Class SyncCheckFailedProductsJob.
 */
class SyncProductSalePricesJob extends UniqueJob
{
    public function handle(): void
    {
        PriceInformation::query()
            ->with(['magentoConnection', 'product', 'prices'])
            ->where(['special_price_sync_required' => true])
            ->chunkById(10, function ($products) {
                collect($products)->each(function (PriceInformation $magentoProduct) {
                    MagentoApi::postProductsSpecialPrice(
                        $magentoProduct->magentoConnection->base_url,
                        $magentoProduct->magentoConnection->api_access_token,
                        $magentoProduct->magentoConnection->magento_store_id ?? 0,
                        $magentoProduct->product->sku,
                        $magentoProduct->prices->sale_price,
                        $magentoProduct->prices->sale_price_start_date->format('Y-m-d H:i:s'),
                        $magentoProduct->prices->sale_price_end_date->format('Y-m-d H:i:s'),
                    );

                    $magentoProduct->update([
                        'special_price_sync_required' => null,
                        'special_prices_fetched_at' => null,
                        'special_prices_raw_import' => null,
                        'magento_sale_price' => null,
                        'magento_sale_price_start_date' => null,
                        'magento_sale_price_end_date' => null,
                    ]);
                });
            });
    }
}
