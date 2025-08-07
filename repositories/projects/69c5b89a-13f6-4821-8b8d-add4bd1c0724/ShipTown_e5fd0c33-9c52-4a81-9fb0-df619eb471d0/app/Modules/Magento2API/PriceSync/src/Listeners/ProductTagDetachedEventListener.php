<?php

namespace App\Modules\Magento2API\PriceSync\src\Listeners;

use App\Events\Product\ProductTagDetachedEvent;
use App\Modules\Magento2API\PriceSync\src\Models\PriceInformation;

class ProductTagDetachedEventListener
{
    public function handle(ProductTagDetachedEvent $event): void
    {
        if ($event->tag() === 'Available Online') {
            PriceInformation::query()
                ->where(['product_id' => $event->product->id])
                ->delete();
        }
    }
}
