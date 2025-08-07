<?php

namespace App\Modules\Magento2API\InventorySync\src\Listeners;

use App\Events\Product\ProductTagDetachedEvent;
use App\Modules\Magento2API\InventorySync\src\Models\Magento2msiProduct;

class ProductTagDetachedEventListener
{
    public function handle(ProductTagDetachedEvent $event)
    {
        if ($event->tag() === 'Available Online') {
            Magento2msiProduct::query()
                ->where(['product_id' => $event->product->id])
                ->delete();
        }
    }
}
