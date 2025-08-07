<?php

namespace App\Modules\Magento2API\PriceSync\src\Http\Controllers;

use App\Abstracts\ReportController;
use App\Modules\Magento2API\PriceSync\src\Models\PriceInformation;
use App\Modules\Reports\src\Services\ReportService;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;

class PriceInformationController extends ReportController
{
    public function index(Request $request): mixed
    {
        $query = PriceInformation::query();

        $report = ReportService::fromQuery($query)
            ->addField('connection_id', 'connection_id')
            ->addField('exists_in_magento', 'exists_in_magento')
            ->addField('product_price_id', 'product_price_id')
            ->addField('product_id', 'product_id')
            ->addField('magento_price', 'magento_price')
            ->addField('magento_sale_price', 'magento_sale_price')
            ->addField('magento_sale_price_start_date', 'magento_sale_price_start_date')
            ->addField('magento_sale_price_end_date', 'magento_sale_price_end_date')
            ->addField('base_price_sync_required', 'base_price_sync_required')
            ->addField('base_prices_fetched_at', 'base_prices_fetched_at')
            ->addField('base_prices_raw_import', 'base_prices_raw_import')
            ->addField('special_price_sync_required', 'special_price_sync_required')
            ->addField('special_prices_fetched_at', 'special_prices_fetched_at')
            ->addField('special_prices_raw_import', 'special_prices_raw_import')
            ->addFilter(AllowedFilter::exact('sku', 'sku'))
            ->addFilter(AllowedFilter::exact('product_sku', 'sku'));

        return $report->response();
    }
}
