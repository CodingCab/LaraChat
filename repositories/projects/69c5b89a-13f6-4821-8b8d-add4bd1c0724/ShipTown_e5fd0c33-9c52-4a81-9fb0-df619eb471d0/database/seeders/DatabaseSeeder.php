<?php

namespace Database\Seeders;

use App\Jobs\DispatchEveryDayEventJob;
use App\Jobs\DispatchEveryFiveMinutesEventJob;
use App\Jobs\DispatchEveryHourEventJobs;
use App\Jobs\DispatchEveryMinuteEventJob;
use App\Jobs\DispatchEveryTenMinutesEventJob;
use App\Modules\AutoStatusRefill\src\AutoStatusRefillServiceProvider;
use App\Modules\AutoStatusRefill\src\Models\Automation;
use App\Modules\InventoryMovementsDailyStatistics\src\InventoryMovementsDailyStatisticsServiceProvider;
use App\Modules\ScurriAnpost\database\seeders\ScurriAnpostSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('up');

        $this->call([
            Demo\ConfigurationSeeder::class,
            Demo\NavigationSeeder::class,
            Demo\OrderStatusesSeeder::class,
            Demo\UsersSeeder::class,
            Demo\OrderAddressesSeeder::class,

            WarehousesSeeder::class,

            Demo\ProductsSeeder::class,
            Demo\ProductsTagsSeeder::class,
            Demo\ProductsPricesSeeder::class,
            InventorySeeder::class,
            Demo\QuantityDiscountSeeder::class,
            CsvProductImportsSeeder::class,

            // Orders Seeders
            Demo\PaidOrdersSeeder::class,
            Demo\PaidPickedOrdersSeeder::class,
            Demo\CollectionOrdersSeeder::class,
            Demo\TestOrdersSeeder::class,
            AssemblyOrderSeeder::class,
            PaidTodaysOrdersFromStockSeeder::class,
            Demo\AssemblyProductOrderSeeder::class,
            OrderWithWeightSeeder::class,

            // Data Collector Seeders
            Demo\DataCollections\TransferToCorkBranchSeeder::class,
            Demo\DataCollections\TransfersFromWarehouseSeeder::class,
            Demo\DataCollections\ArchivedTransfersFromWarehouseSeeder::class,
            Demo\DataCollections\TransactionInProcessSeeder::class,

            SalesSeeder::class,
            StocktakeSuggestionsSeeder::class,

            PrintNodeClientSeeder::class,

            // Shipping Services Seeders
            DpdIrelandSeeder::class,
            DpdUKSeeder::class,
            DpdPolandSeeder::class,
            RabenGroupSeeder::class,
            ScurriAnpostSeeder::class,
            InPostPolandSeeder::class,


            // Modules Seeders
            Modules\Slack\ConfigurationSeeder::class,
            Modules\Magento2MSI\ConnectionSeeder::class,
            Modules\Magento2API\ConnectionSeeder::class,
            FakturowoSeeder::class,
            Api2cartSeeder::class,

            RestockingReportSeeder::class,
            RestockingProductsSeeder::class,

            PaymentTypesSeeder::class,
            SalesTaxesSeeder::class,
            //            DataCollectionsSeeder::class,
            //            RmsapiConnectionSeeder::class,
            //            AutomationsSeeder::class,

            //            ProductAliasSeeder::class,
            //            ProductTagsSeeder::class,
            //            SplitOrdersScenarioSeeder::class,
            //            Orders_PackingWebDemoSeeder::class,
            //            Orders_StorePickupDemoSeeder::class,
            //            UnpaidOrdersSeeder::class,
            //            ClosedOrdersSeeder::class,
            //            PicksSeeder::class,
            //            OrderShipmentsSeeder::class,

        ]);

        Automation::query()->updateOrCreate([
            'id' => 1,
        ], [
            'from_status_code' => 'paid',
            'to_status_code' => 'picking',
            'desired_order_count' => 10,
            'refill_only_at_0' => true,
        ]);

        AutoStatusRefillServiceProvider::enableModule();
        InventoryMovementsDailyStatisticsServiceProvider::enableModule();

        DispatchEveryMinuteEventJob::dispatch();
        DispatchEveryFiveMinutesEventJob::dispatch();
        DispatchEveryTenMinutesEventJob::dispatch();
        DispatchEveryHourEventJobs::dispatch();
        DispatchEveryDayEventJob::dispatch();
    }
}
