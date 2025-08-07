<?php

namespace App\Modules\Fakturowo\src\Models;

use App\BaseModel;
use App\Models\OrderProduct;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\InvoiceOrderProduct
 *
 * @property int $id
 * @property int $invoice_id
 * @property int $orders_products_id
 * @property float $quantity_invoiced
 *
 * @property OrderProduct $orderProduct
 * @property Invoice $invoice
 */
class InvoiceOrderProduct extends BaseModel
{
    protected $table = 'modules_fakturowo_invoices_orders_products';

    protected $fillable = [
        'invoice_id',
        'order_id',
        'orders_products_id',
        'quantity_invoiced',
    ];

    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class, 'orders_products_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
