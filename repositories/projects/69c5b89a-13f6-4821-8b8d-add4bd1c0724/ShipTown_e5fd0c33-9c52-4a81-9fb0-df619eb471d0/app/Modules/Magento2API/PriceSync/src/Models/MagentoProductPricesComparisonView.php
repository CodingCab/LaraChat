<?php

namespace App\Modules\Magento2API\PriceSync\src\Models;

use App\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property PriceInformation $magentoProduct
 * @property int $magento_store_id
 * @property string $sku
 * @property float $expected_price
 * @property float $expected_sale_price
 * @property Carbon $expected_sale_price_start_date
 * @property Carbon $expected_sale_price_end_date
 */
class MagentoProductPricesComparisonView extends BaseModel
{
    protected $table = 'modules_magento2api_products_prices_comparison_view';

    protected function casts(): array
    {
        return [
            'expected_price' => 'double',
            'expected_sale_price' => 'double',
            'magento_sale_price_start_date' => 'datetime',
            'expected_sale_price_start_date' => 'datetime',
            'magento_sale_price_end_date' => 'datetime',
            'expected_sale_price_end_date' => 'datetime',
        ];
    }

    public function magentoProduct(): BelongsTo
    {
        return $this->belongsTo(PriceInformation::class, 'modules_magento2api_products_id');
    }
}
