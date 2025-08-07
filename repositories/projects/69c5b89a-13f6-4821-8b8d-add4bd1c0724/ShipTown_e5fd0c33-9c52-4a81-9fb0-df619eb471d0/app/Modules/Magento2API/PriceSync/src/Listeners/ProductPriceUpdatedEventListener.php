<?php

namespace App\Modules\Magento2API\PriceSync\src\Listeners;

use App\Events\Product\ProductPriceUpdatedEvent;
use App\Modules\Magento2API\PriceSync\src\Models\PriceInformation;

class ProductPriceUpdatedEventListener
{
    public function handle(ProductPriceUpdatedEvent $event): void
    {
        PriceInformation::query()
            ->where(['product_price_id' => $event->product_price->getKey()])
            ->update([
                'base_price_sync_required' => null,
                'special_price_sync_required' => null,
            ]);
    }
}
