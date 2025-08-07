<?php

/*
|--------------------------------------------------------------------------
| User Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Admin\Settings\Modules\Webhooks\SubscriptionController;
use App\Http\Controllers\Api\BarcodeGeneratorController;
use App\Http\Controllers\Api\QuantityDiscountsController;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Csv;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataCollectorController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FulfillmentDashboardController;
use App\Http\Controllers\MailTemplatePreviewController;
use App\Http\Controllers\Order;
use App\Http\Controllers\PdfOrderController;
use App\Http\Controllers\ProductsMergeController;
use App\Http\Controllers\Reports;
use App\Http\Controllers\ScreenshotsController;
use App\Http\Controllers\ShippingLabelController;
use App\Http\Controllers\FlowchartSampleController;
use App\Modules\Fakturowo\src\Http\Controllers\Reports\FakturowoInvoicesReport;
use App\Modules\Magento2API\InventorySync\src\Http\Controllers\InventorySourceItemsController;
use App\Modules\Magento2API\PriceSync\src\Http\Controllers\PriceInformationController;
use Illuminate\Support\Facades\Route;

Route::view('splash-text', 'splash-text')->name('splash-text');
Route::view('/', 'landing')->middleware('guest')->name('landing');
Route::redirect('home', '/')->name('home');
Route::get('flowchart-sample', FlowchartSampleController::class)->name('flowchart-sample');

Route::middleware('auth')->group(function () {
    Route::resource('verify', Auth\TwoFactorController::class)->only(['index', 'store']);
    Route::get('screenshots', [ScreenshotsController::class, 'index']);
    Route::view('quick-connect', 'quick-connect');
    Route::view('quick-connect/magento', 'quick-connect.magento');
    Route::view('quick-connect/shopify', 'quick-connect.shopify');

    Route::view('products/inventory', 'pages-manager')->name('products.inventory');
    Route::view('products/transfers-in', 'pages-manager')->name('products.transfers-in');
    Route::view('products/transfers-out', 'pages-manager')->name('products.transfers-out');
    Route::view('products/offline-inventory', 'pages-manager')->name('products.offline-inventory');
    Route::view('products/purchase-orders', 'pages-manager')->name('products.purchase-orders');
    Route::view('products/transactions', 'pages-manager')->name('products.transactions');
    Route::view('products/stocktaking', 'pages-manager')->name('products.stocktaking');

    Route::view('orders', 'pages-manager')->name('orders');

    Route::view('tools/picklist', 'pages-manager')->name('tools.picklist');
    Route::view('tools/packlist', 'pages-manager')->name('tools.packlist');
    Route::view('tools/data-collector', 'pages-manager')->name('tools.data-collector');
    Route::view('tools/shelf-labels', 'pages-manager')->name('tools.shelf-labels');
    Route::view('tools/restocking', 'pages-manager')->name('tools.restocking');

    Route::apiResource('barcode-generator', BarcodeGeneratorController::class)->only(['index']);
    Route::resource('documents', DocumentController::class, ['index'])->only(['index']);

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('fulfillment-dashboard', [FulfillmentDashboardController::class, 'index'])->name('fulfillment-dashboard');
    Route::get('inventory-dashboard', [Reports\InventoryDashboardController::class, 'index'])->name('inventory-dashboard');
    Route::get('products-merge', [ProductsMergeController::class, 'index'])->name('products-merge');
    Route::view('fulfillment-statistics', 'fulfillment-statistics')->name('fulfillment-statistics');
    Route::view('setting-profile', 'setting-profile')->name('setting-profile');
    Route::view('data-collector', 'pages-manager')->name('data-collector');
    Route::get('data-collector/{data_collection_id}', [DataCollectorController::class, 'index'])->name('data-collector-show');
    Route::get('shipping-labels/{shipping_label}', [ShippingLabelController::class, 'show'])->name('shipping-labels');

    Route::resource('order/packsheet', Order\PacksheetController::class)->only(['show']);
    Route::view('tools/printer', 'pages-manager')->name('tools.printer');

    Route::as('tools.')->group(function () {
        Route::view('tools/data-collector/transaction', 'pages-manager')->name('point_of_sale');
    });

    Route::as('modules.')->group(function () {
        Route::view('modules/scheduled-reports', 'modules/scheduled-reports/index')->name('scheduled-reports');
    });

    Route::as('reports.')->group(function () {
        Route::resource('reports/activity-log', Reports\ActivityLogController::class)->only('index');
        Route::resource('reports/inventory-dashboard', Reports\InventoryDashboardController::class)->only('index');
        Route::resource('reports/picks', Reports\PickController::class)->only('index');
        Route::resource('reports/shipments', Reports\ShipmentController::class)->only('index');
        Route::resource('reports/restocking', Reports\RestockingReportController::class)->only('index');
        Route::resource('reports/stocktake-suggestions', Reports\StocktakeSuggestionsController::class)->only('index');
        Route::resource('reports/stocktake-suggestions-totals', Reports\StocktakeSuggestionsTotalsReportController::class)->only('index');
        Route::resource('reports/products-inventory', Reports\InventoryTotalsController::class)->only('index');
        Route::resource('reports/products-required', Reports\ProductsRequiredReportController::class)->only('index');
        Route::resource('reports/purchase-orders', Reports\PurchaseOrdersController::class)->only('index');
        Route::resource('reports/inventory', Reports\InventoryController::class)->only('index');
        Route::resource('reports/inventory-movements-summary', Reports\InventoryMovementsSummaryController::class)->only('index');
        Route::resource('reports/inventory-sales-summary', Reports\InventorySalesSummaryController::class)->only('index');
        Route::resource('reports/inventory-stocktakes', Reports\InventoryMovementController::class)->only('index');
        Route::resource('reports/inventory-transferred', Reports\InventoryTransfersController::class)->only('index');
        Route::resource('reports/inventory-movements', Reports\InventoryMovementController::class)->only('index');
        Route::resource('reports/inventory-movements-daily-statistics', Reports\InventoryMovementsDailyStatisticsReportController::class)->only('index');
        Route::resource('reports/inventory-reservations', Reports\InventoryReservationsController::class)->only('index');
        Route::resource('reports/orders', Reports\OrderController::class)->only('index');
        Route::resource('reports/order-fulfillment-time', Reports\OrderFulfillmentTimeReportController::class)->only('index');
        Route::resource('reports/order-products', Reports\OrderProductController::class)->only('index');
        Route::resource('reports/data-collections', Reports\DataCollectionsReport::class)->only('index');
        Route::resource('reports/data-collections-records', Reports\DataCollectionsRecordsReport::class)->only('index');
        Route::resource('reports/fakturowo-invoices', FakturowoInvoicesReport::class)->only('index');
        Route::resource('reports/heartbeats', Reports\HeartbeatsReportController::class)->only('index');
        Route::resource('reports/assembly-products-elements', Reports\AssemblyProductsElementsController::class)->only('index');
    });

    Route::get('pdf/orders/{order_number}/{template}', [PdfOrderController::class, 'show']);
    Route::get('csv/ready_order_shipments', [Csv\ReadyOrderShipmentController::class, 'index'])->name('ready_order_shipments_as_csv');
    Route::get('csv/order_shipments', [Csv\PartialOrderShipmentController::class, 'index'])->name('partial_order_shipments_as_csv');
    Route::get('csv/products/picked', [Csv\ProductsPickedInWarehouse::class, 'index'])->name('warehouse_picks.csv');
    Route::get('csv/products/shipped', [Csv\ProductsShippedFromWarehouseController::class, 'index'])->name('warehouse_shipped.csv');
    Route::get('csv/boxtop/stock', [Csv\BoxTopStockController::class, 'index'])->name('boxtop-warehouse-stock.csv');
    Route::view('settings/warehouses', 'settings/warehouses')->name('settings.warehouses');

    Route::middleware(['role:admin'])->group(function () {
        Route::view('settings/modules/magento2api/inventory-sync', 'settings.modules.magento2api');
        Route::view('settings/modules/magento2api/price-sync', 'settings/magento-api')->name('settings.modules.magento-api');
        Route::resource('settings/modules/magento2api/price-information', PriceInformationController::class)->only(['index']);
        Route::resource('settings/modules/magento2api/inventory-source-items', InventorySourceItemsController::class)->only(['index']);
        Route::view('settings/modules/stocktake-suggestions', 'settings/modules/stocktake-suggestions');
        Route::view('settings/modules/active-orders-inventory-reservations', 'settings/modules/active-orders-inventory-reservations');

        Route::view('settings', 'settings')->name('settings');
        Route::view('settings/general', 'settings/general')->name('settings.general');
        Route::view('settings/order-statuses', 'settings/order-statuses')->name('settings.order_statuses');
        Route::view('settings/printnode', 'settings/printnode')->name('settings.printnode');
        Route::view('settings/rmsapi', 'settings/rmsapi')->name('settings.rmsapi');
        Route::view('settings/dpd-ireland', 'settings/dpd-ireland')->name('settings.dpd-ireland');
        Route::view('settings/api2cart', 'settings/api2cart')->name('settings.api2cart');
        Route::view('settings/api', 'settings/api')->name('settings.api');
        Route::view('settings/chatgpt', 'settings/chatgpt')->name('settings.chatgpt');
        Route::view('settings/users', 'settings/users')->name('settings.users');
        Route::view('settings/mail-templates', 'settings/mail-templates')->name('settings.mail_templates');
        Route::get('settings/mail-templates/{mailTemplate}/preview', [MailTemplatePreviewController::class, 'index'])->name('settings.mail_template_preview');
        Route::view('settings/navigation-menu', 'settings/navigation-menu')->name('settings.navigation_menu');
        Route::get('settings/automations', [\App\Http\Controllers\AutomationsController::class, 'index'])->name('settings.automations');
        Route::view('settings/modules', 'settings/modules')->name('settings.modules');
        Route::view('settings/modules/dpd-uk', 'settings/dpd-uk')->name('settings.modules.dpd-uk');
        Route::view('settings/modules/couriers/dpd-poland', 'settings/modules/couriers/dpd-poland')->name('settings.modules.couriers.dpd-poland');
        Route::get('settings/modules/webhooks/subscriptions', [SubscriptionController::class, 'index'])->name('webhooks::subscriptions');
        Route::view('modules/slack/config', 'modules/slack/config')->name('modules.slack.config');
        Route::view('settings/modules/quantity-discounts', 'settings/modules/quantity-discounts/index')->name('settings.modules.quantity-discounts.index');
        Route::get('settings/modules/quantity-discounts/{id}', [QuantityDiscountsController::class, 'edit'])->name('settings.modules.quantity-discounts.edit');
        Route::view('settings/modules/data-collector-payments', 'settings/modules/payments/index')->name('settings.modules.payments.index');
        Route::view('settings/modules/data-collector-discounts', 'settings/modules/discounts/index')->name('settings.modules.discounts.index');
        Route::view('settings/modules/sales-taxes', 'settings/modules/sales-taxes/index')->name('settings.modules.sales-taxes.index');
        Route::view('settings/modules/auto-picking-refilling', 'settings/modules/auto-picking-refilling')->name('settings.modules.auto-picking-refilling.index');
        Route::view('settings/modules/permissions', 'settings/modules/permissions/index')->name('settings.modules.permissions.index');
        Route::view('settings/modules/point-of-sale-configuration', 'settings/modules/point-of-sale-configuration/index')->name('settings.modules.point-of-sale-configuration.index');
        Route::view('settings/modules/fakturowo', 'settings/modules/fakturowo/index')->name('settings.modules.fakturowo.index');
    });
});
