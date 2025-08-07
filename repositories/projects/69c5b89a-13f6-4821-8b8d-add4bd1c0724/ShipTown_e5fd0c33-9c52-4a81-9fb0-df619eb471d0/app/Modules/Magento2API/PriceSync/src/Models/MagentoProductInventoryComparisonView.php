<?php

namespace App\Modules\Magento2API\PriceSync\src\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property PriceInformation $magentoProduct
 * @property float $expected_quantity
 */
class MagentoProductInventoryComparisonView extends BaseModel
{
    protected $table = 'modules_magento2api_products_inventory_comparison_view';

    public function magentoProduct(): BelongsTo
    {
        return $this->belongsTo(PriceInformation::class, 'modules_magento2api_products_id');
    }
}
