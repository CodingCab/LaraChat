<?php

namespace App\Modules\Fakturowo\src\OrderActions;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use App\Modules\Fakturowo\src\Api\FakturowoApi;
use App\Modules\Fakturowo\src\Models\FakturowoConfiguration;
use App\Modules\Fakturowo\src\Models\Invoice;
use App\Modules\Fakturowo\src\Models\InvoiceOrderProduct;
use App\Modules\Fakturowo\src\Services\FakturowoService;
use Exception;
use Illuminate\Support\Facades\DB;

class RaiseFakturowoPLInvoice extends BaseOrderActionAbstract
{
    public function handle(string $options = ''): bool
    {
        $fakturowoConnection = FakturowoConfiguration::query()->where('connection_code', $options)->first();

        if (!$fakturowoConnection) {
            return false;
        }

        $productsToInvoice = OrderProduct::query()
            ->where('order_id', $this->order->id)
            ->where('quantity_shipped', '>', 0)
            ->where('quantity_invoiced', '<', DB::raw('quantity_shipped'))
            ->get();

        if ($productsToInvoice->isEmpty()) {
            return false;
        }

        $productsToInvoice->each(function (OrderProduct $orderProduct) {
            $this->insertRecord($orderProduct);
        });

        try {
            $fakturowoInvoice = $this->sendInvoiceToFakturowo($this->order->id, $fakturowoConnection);

            activity()
                ->causedByAnonymous()
                ->performedOn($this->order)
                ->log('Fakturowo.pl - Wystawiono fakturę');

            return true;
        } catch (Exception $e) {
            // Log the error
            activity()
                ->causedByAnonymous()
                ->performedOn($this->order)
                ->log('Fakturowo.pl - Błąd podczas wystawiania faktury: ' . $e->getMessage());

            // Re-throw the exception to ensure the automation knows the action failed
            throw $e;
        }
    }

    private function sendInvoiceToFakturowo(int $order_id, FakturowoConfiguration $fakturowoConnection): ?Invoice
    {
        $productsToInvoice = InvoiceOrderProduct::query()
            ->where('order_id', $order_id)
            ->whereNull('invoice_id')
            ->get();

        if ($productsToInvoice->isEmpty()) {
            return null;
        }

        $addDeliveryCharge = Invoice::query()->where('order_id', $order_id)->doesntExist();

        $payload = FakturowoService::prepareInvoiceData(Order::find($order_id), $productsToInvoice, $addDeliveryCharge);

        // First, try to create the invoice on Fakturowo API
        $fakturowoInvoice = FakturowoApi::postInvoice(
            $fakturowoConnection->api_key,
            $payload,
            $fakturowoConnection->api_url
        );

        // Only create the invoice record after successful API response
        /** @var Invoice $invoice */
        $invoice = Invoice::query()->create([
            'order_id' => $order_id,
            'filename' => $fakturowoInvoice->filename,
            'fakturowo_invoice_id' => $fakturowoInvoice->invoiceId,
            'fakturowo_invoice_url' => $fakturowoInvoice->invoiceUrl,
        ]);

        InvoiceOrderProduct::query()
            ->where('order_id', $order_id)
            ->whereNull('invoice_id')
            ->update(['invoice_id' => $invoice->id]);

        return $invoice;
    }

    private function insertRecord(OrderProduct $orderProduct): void
    {
        DB::transaction(function () use ($orderProduct) {
            $quantityToInvoice = $orderProduct->quantity_shipped - $orderProduct->quantity_invoiced;

            InvoiceOrderProduct::query()->create([
                'order_id' => $orderProduct->order_id,
                'orders_products_id' => $orderProduct->id,
                'quantity_invoiced' => $quantityToInvoice,
            ]);

            $orderProduct->update([
                'quantity_invoiced' => $orderProduct->quantity_invoiced + $quantityToInvoice
            ]);
        });
    }
}
