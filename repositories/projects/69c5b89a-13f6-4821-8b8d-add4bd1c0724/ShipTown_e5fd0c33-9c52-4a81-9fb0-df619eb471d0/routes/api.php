<?php

use App\Http\Controllers\Api;
use App\Services\RouteService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::name('api.')->group(function () {
    RouteService::apiResource('activities', Api\ActivityController::class, ['index', 'store']);
    RouteService::apiResource('admin/user/roles', Api\UserRoleController::class, ['index', 'store', 'destroy']);
    RouteService::apiResource('admin/users', Api\UserController::class, ['index', 'store', 'update', 'destroy', 'show']);
    RouteService::apiResource('configurations', Api\ConfigurationController::class, ['index', 'store']);
    RouteService::apiResource('csv-import', Api\CsvImportController::class, ['store']);
    RouteService::apiResource('csv-import/data-collections', Api\CsvImport\DataCollectionsImportController::class, ['store']);
    RouteService::apiResource('csv-import/products', Api\CsvImport\ProductsImportController::class, ['index', 'store']);
    RouteService::apiResource('data-collector-actions/add-product', Api\DataCollectorActions\AddProductController::class, ['store']);
    RouteService::apiResource('data-collector-actions/import-as-stocktake', Api\DataCollectorActions\ImportAsStocktakeController::class, ['store']);
    RouteService::apiResource('data-collector-actions/import-as-sale-inventory-movement', Api\DataCollectorActions\ImportAsSaleInventoryMovementController::class, ['store']);
    RouteService::apiResource('data-collector-records', Api\DataCollectorRecordController::class, ['index', 'store', 'update']);
    RouteService::apiResource('data-collector', Api\DataCollectorController::class);
    RouteService::apiResource('data-collector/comments', Api\DataCollectionCommentController::class, ['index', 'store']);
    RouteService::apiResource('data-collector-payment-types', Api\DataCollectorPaymentTypeController::class, ['index', 'store', 'destroy']);
    RouteService::apiResource('data-collector-payments', Api\DataCollectorPaymentController::class, ['index', 'store', 'update']);
    RouteService::apiResource('data-collector-discounts', Api\DataCollectorDiscountController::class, ['index', 'store', 'destroy']);
    RouteService::apiResource('heartbeats', Api\HeartbeatsController::class, ['index']);
    RouteService::apiResource('inventory-movements', Api\InventoryMovementController::class, ['store', 'index']);
    RouteService::apiResource('inventory', Api\InventoryController::class, ['index', 'store']);
    RouteService::apiResource('jobs', Api\JobsController::class, ['store', 'index']);
    RouteService::apiResource('logs', Api\LogController::class, ['index']);
    RouteService::apiResource('mail-templates', Api\MailTemplateController::class, ['index', 'store', 'update']);
    RouteService::apiResource('modules', Api\ModuleController::class, ['index', 'update']);
    RouteService::apiResource('modules/active-orders-inventory-reservations/configuration', App\Modules\ActiveOrdersInventoryReservations\src\Http\Controllers\Api\Modules\ActiveOrdersInventoryReservationsController::class, ['index', 'update']);
    RouteService::apiResource('modules/api2cart/connections', Api\Modules\Api2cart\Api2cartConnectionController::class, ['index', 'store', 'destroy']);
    RouteService::apiResource('modules/api2cart/products', Api\Modules\Api2cart\ProductsController::class, ['index']);
    RouteService::apiResource('modules/automations/config', Api\Modules\OrderAutomations\ConfigController::class, ['index']);
    RouteService::apiResource('modules/automations', Api\Modules\OrderAutomations\AutomationController::class, ['index', 'store', 'update', 'destroy', 'show']);
    Route::post('modules/automations/{automation}/copy', [Api\Modules\OrderAutomations\AutomationController::class, 'copy'])->name('modules.automations.copy');
    RouteService::apiResource('modules/autostatus/picking/configuration', Api\Modules\AutoStatus\ConfigurationController::class, ['index', 'store', 'destroy']);
    RouteService::apiResource('modules/dpd-ireland/connections', Api\Modules\DpdIreland\DpdIrelandController::class, ['index', 'store', 'destroy']);
    RouteService::apiResource('modules/dpd-uk/dpd-uk-connections', Api\Modules\DpdUk\DpdUkConnectionController::class, ['index', 'store', 'destroy']);
    RouteService::apiResource('modules/magento-api/connections', Api\Modules\MagentoApi\MagentoApiConnectionController::class);
    RouteService::apiResource('modules/magento2msi/connections', Api\Modules\Magento2msi\Magento2MsiConnectionController::class);
    RouteService::apiResource('modules/printnode/clients', Api\Modules\Printnode\ClientController::class, ['index', 'store', 'destroy']);
    RouteService::apiResource('modules/printnode/printers', Api\Modules\Printnode\PrinterController::class, ['index']);
    RouteService::apiResource('modules/printnode/printjobs', Api\Modules\Printnode\PrintJobController::class, ['store']);
    RouteService::apiResource('modules/rms_api/connections', Api\Modules\Rmsapi\RmsapiConnectionController::class, ['index', 'store', 'destroy']);
    RouteService::apiResource('modules/slack/config', Api\Modules\Slack\ConfigController::class, ['index', 'store']);
    RouteService::apiResource('modules/stocktake-suggestions/configuration', Api\Modules\StocktakeSuggestions\ConfigurationController::class, ['index', 'store']);
    RouteService::apiResource('modules/webhooks/subscriptions', Api\Modules\Webhooks\SubscriptionController::class, ['index', 'store']);
    RouteService::apiResource('modules/scheduled-reports', Api\Modules\ScheduledReport\ScheduledReportController::class);
    RouteService::apiResource('modules/sales-taxes', Api\Modules\SalesTaxes\SaleTaxController::class);
    RouteService::apiResource('modules/chatgpt/generate-products-descriptions', Api\Modules\ChatGPT\GenerateProductDescriptionController::class, ['store']);
    RouteService::apiResource('modules/chatgpt/translate-products-descriptions', Api\Modules\ChatGPT\TranslateProductDescriptionController::class, ['store']);
    Route::post('modules/chatgpt/chat', [Api\Modules\ChatGPT\ChatController::class, 'store']);
    RouteService::apiResource('modules/permissions', Api\Modules\Permissions\PermissionController::class, ['index']);
    RouteService::apiResource('modules/point-of-sale-configuration', Api\Modules\PointOfSaleConfiguration\PointOfSaleConfigurationController::class, ['index', 'update']);
    RouteService::apiResource('modules/fakturowo/configuration', \App\Modules\Fakturowo\src\Http\Controllers\Api\Modules\FakturowoConfigurationController::class, ['index', 'store', 'destroy']);
    RouteService::apiResource('navigation-menu', Api\NavigationMenuController::class);
    RouteService::apiResource('order/addresses', Api\OrderAddressController::class, ['index', 'store', 'update']);
    RouteService::apiResource('order/comments', Api\OrderCommentController::class, ['index', 'store']);
    RouteService::apiResource('order/tags', Api\OrderTagController::class, ['index', 'store']);
    RouteService::apiResource('order/payments', Api\OrderPaymentController::class, ['index']);
    RouteService::apiResource('order/products', Api\OrderProductController::class, ['index', 'update']);
    RouteService::apiResource('order/shipments', Api\OrderShipmentController::class, ['index', 'store']);
    RouteService::apiResource('orders-statuses', Api\OrderStatusController::class);
    RouteService::apiResource('orders', Api\OrderController::class, ['index', 'store', 'update', 'show']);
    RouteService::apiResource('orders/products/shipments', Api\OrderProductShipmentController::class, ['store']);
    RouteService::apiResource('packlist/order', Api\PacklistOrderController::class, ['index']);
    RouteService::apiResource('picklist', Api\PicklistController::class, ['index']);
    RouteService::apiResource('picklist/picks', Api\Picklist\PicklistPickController::class, ['store', 'destroy']);
    RouteService::apiResource('print-jobs', Api\PrintJobController::class, ['store']);
    RouteService::apiResource('product/aliases', Api\ProductAliasController::class, ['index']);
    RouteService::apiResource('product/descriptions', Api\ProductDescriptionController::class, ['store']);
    RouteService::apiResource('product/tags', Api\ProductTagController::class, ['index', 'store']);
    RouteService::apiResource('products-aliases', Api\ProductAliasController::class);
    RouteService::apiResource('products', Api\ProductController::class, ['index', 'store', 'update']);
    RouteService::apiResource('quantity-discount-product', Api\QuantityDiscountProductsController::class, ['index', 'store', 'destroy']);
    RouteService::apiResource('quantity-discounts', Api\QuantityDiscountsController::class);
    RouteService::apiResource('reports/inventory-transfers', Api\Reports\InventoryTransfersController::class, ['index']);
    RouteService::apiResource('reports/inventory', Api\Reports\InventoryController::class, ['index']);
    RouteService::apiResource('reports/picks', Api\Reports\PicksController::class, ['index']);
    RouteService::apiResource('reports/heartbeats', Api\Reports\HeartbeatsController::class, ['index']);
    RouteService::apiResource('reports/assembly-products-elements', Api\Reports\AssemblyProductsElementsController::class, ['index']);
    RouteService::apiResource('reports/stocktake-suggestions', Api\Reports\StockTakeSuggestionsController::class, ['index']);
    RouteService::apiResource('reports/products-required', Api\Reports\ProductsRequiredController::class, ['index']);
    RouteService::apiResource('restocking', Api\RestockingController::class, ['index']);
    RouteService::apiResource('settings/modules/automations/run', Api\Modules\OrderAutomations\RunAutomationController::class, ['store']);
    RouteService::apiResource('settings/user/me', Api\UserMeController::class, ['index', 'store']);
    RouteService::apiResource('settings/widgets', Api\WidgetController::class, ['store', 'update']);
    RouteService::apiResource('shipments', Api\ShipmentController::class, ['store']);
    RouteService::apiResource('shipping-labels', Api\ShippingLabelController::class, ['store']);
    RouteService::apiResource('shipping-services', Api\ShippingServiceController::class, ['index']);
    RouteService::apiResource('stocktake-suggestions-details', Api\StocktakeSuggestionDetailController::class, ['index']);
    RouteService::apiResource('stocktake-suggestions', Api\StocktakeSuggestionController::class, ['index']);
    RouteService::apiResource('stocktakes', Api\StocktakesController::class, ['store']);
    RouteService::apiResource('transactions', Api\TransactionController::class, ['update']);
    RouteService::apiResource('warehouses', Api\WarehouseController::class);
    RouteService::apiResource('assemble-products', Api\AssembleProduct::class, ['store']);
    RouteService::apiResource('disassemble-products', Api\DisassembleProduct::class, ['store']);
    RouteService::apiResource('assembly-product-elements', Api\AssemblyProductElementsController::class, ['store', 'update', 'destroy']);
});

Route::post('pdf/download', [Api\PDF\PdfDownloadController::class, 'update'])->name('api.pdf.download');
Route::post('pdf/preview', [Api\PDF\PdfPreviewController::class, 'update'])->name('api.pdf.preview');
Route::post('pdf/print', [Api\PDF\PdfPrintController::class, 'update'])->name('api.pdf.print');
Route::post('transaction/receipt-print', [Api\TransactionController::class, 'printReceipt'])->name('api.transaction.receipt-print');
Route::get('transaction/receipt-preview', [Api\TransactionController::class, 'previewReceipt'])->name('api.transaction.receipt-preview');
Route::post('transaction/receipt', [Api\TransactionController::class, 'sendReceipt'])->name('api.transaction.receipt');
Route::put('print/order/{order_number}/{view}', [Api\PrintOrderController::class, 'update'])->name('api.print.order');
Route::put('modules/permissions/bulk-update', [Api\Modules\Permissions\PermissionController::class, 'updatePermissions'])->name('api.modules.permissions.bulkUpdate');
