<?php

namespace App\Modules\Api2cart\src\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CheckForOutOfSyncProductsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle()
    {
        $this->invalidateIfQuantityNotMatch();
        $this->invalidateIfPriceNotMatch();
        $this->invalidaseIfSalePricingNotMatch();
    }

    private function invalidateIfQuantityNotMatch(): void
    {
        DB::statement('
            UPDATE modules_api2cart_product_links as product_link

            INNER JOIN modules_api2cart_product_quantity_comparison_view as comparison_view
              ON product_link.id = comparison_view.product_link_id
              AND comparison_view.product_link_is_in_sync = 1
              AND comparison_view.quantity_api2cart != comparison_view.quantity_expected

            SET product_link.is_in_sync = 0
        ');
    }

    private function invalidateIfPriceNotMatch(): void
    {
        DB::statement('
            UPDATE modules_api2cart_product_links as product_link

            INNER JOIN modules_api2cart_product_pricing_comparison_view as comparison_view
              ON product_link.id = comparison_view.product_link_id
              AND comparison_view.product_link_is_in_sync = 1
              AND comparison_view.api2cart_price != comparison_view.actual_price

            SET product_link.is_in_sync = 0

            WHERE product_link.api2cart_quantity > 0
        ');
    }

    private function invalidaseIfSalePricingNotMatch(): void
    {
        DB::statement('
            UPDATE modules_api2cart_product_links as product_link

            INNER JOIN modules_api2cart_product_pricing_comparison_view as comparison_view
              ON product_link.id = comparison_view.product_link_id
                AND product_link.api2cart_quantity > 0
                AND comparison_view.product_link_is_in_sync = 1
                AND (
                    # we will select ony records with promotions that are scheduled or currently active on api2cart
                    comparison_view.actual_sale_price_end_date > now()
                    OR comparison_view.api2cart_sale_price_end_date > now()
                )
                AND (
                    comparison_view.api2cart_sale_price != comparison_view.actual_sale_price
                    OR comparison_view.api2cart_sale_price_start_date != comparison_view.actual_sale_price_start_date
                    OR comparison_view.api2cart_sale_price_end_date != comparison_view.actual_sale_price_end_date
                )

            SET product_link.is_in_sync = 0
        ');
    }
}
