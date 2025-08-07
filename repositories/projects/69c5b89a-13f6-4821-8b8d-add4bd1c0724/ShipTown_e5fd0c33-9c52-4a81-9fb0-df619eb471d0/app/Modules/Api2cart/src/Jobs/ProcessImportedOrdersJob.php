<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Order;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use App\Services\OrderService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ProcessImportedOrdersJob extends UniqueJob
{
    public function handle(): void
    {
        do {
            $orderImports = Api2cartOrderImports::query()
                ->whereNull('when_processed')
                ->limit(10)
                ->get();

            if ($orderImports->isEmpty()) {
                // No more orders to process
                break;
            }

            $orderImports->each(function (Api2cartOrderImports $orderImport) {
                $order = $this->createOrderFrom($orderImport);

                $raw_import = $orderImport->raw_import;

                $orderImport->update([
                    'order_id' => $order->id,
                    'order_placed_at' => Carbon::createFromFormat(
                        data_get($raw_import, 'create_at.format'),
                        data_get($raw_import, 'create_at.value')
                    ),
                    'order_number' => data_get($raw_import, 'id'),
                    'api2cart_order_id' => data_get($raw_import, 'order_id'),
                    'shipping_method_name' => data_get($raw_import, 'shipping_method.name', ''),
                    'shipping_method_code' => data_get($raw_import, 'shipping_method.additional_fields.code'),
                    'status_code' => data_get($raw_import, 'status.id', ''),
                    'when_processed' => now(),
                ]);
            });
        } while ($orderImports->isNotEmpty());
    }

    /**
     * @throws Exception
     */
    private function createOrderFrom(Api2cartOrderImports $orderImport): Order
    {
        $data = $orderImport->raw_import;

        $orderExists = Order::query()
            ->where('order_number', data_get($data, 'id'))
            ->exists();

        $uuid = implode('-', ['module-api2cart-connection-id', $orderImport->connection_id, 'remote-record-id', data_get($data, 'order_id')]);

        $orderAttributes = [
            'custom_unique_reference_id' => $uuid,
            'order_number' => data_get($data, 'id'),
            'total' => data_get($data, 'totals.total', 0),
            'total_products' => data_get($data, 'totals.subtotal'),
            'total_shipping' => data_get($data, 'total.shipping_ex_tax', 0) + data_get($data, 'total.additional_fields.shipping_tax', 0),
            'total_discounts' => data_get($data, 'totals.discount', 0),
            'total_paid' => data_get($data, 'total.total_paid', 0) ?? 0,
            'shipping_method_name' => data_get($data, 'shipping_method.name', 0),
            'shipping_method_code' => data_get($data, 'shipping_method.additional_fields.code', 0),
            'order_placed_at' => $orderImport->ordersCreateAt()->tz('UTC'),
            'origin_status_code' => data_get($data, 'status.id', ''),
            'order_products' => $orderImport->extractOrderProducts(),
            'payments' => $orderImport->extractPaymentAttributes(),
            'raw_import' => $data,
        ];

        if (! $orderExists) {
            $orderAttributes['shipping_address'] = $orderImport->extractShippingAddressAttributes();
            $orderAttributes['billing_address'] = $orderImport->extractBillingAddressAttributes();
        }

//        $statusCode = data_get($data, 'status.id');
//        $status = OrderStatus::query()->where('code', $statusCode)->first();
//        if ($status?->sync_ecommerce) {
//            $orderAttributes['status_code'] = $statusCode;
//        }
        $order = OrderService::updateOrCreate($orderAttributes);

        $this->importOrderComments($orderImport, $order);

        return $order;
    }

    private function importOrderComments(Api2cartOrderImports $orderImport, Order $order): void
    {
        foreach ($orderImport->extractOrderComments() as $commentData) {
            DB::table('orders_comments')->updateOrInsert(
                [
                    'order_id' => $order->id,
                    'comment' => $commentData['comment'],
                    'created_at' => $commentData['created_at'],
                ],
                [
                    'updated_at' => $commentData['created_at'],
                    'is_customer' => $commentData['is_customer'] ?? false,
                ]
            );
        }
    }
}
