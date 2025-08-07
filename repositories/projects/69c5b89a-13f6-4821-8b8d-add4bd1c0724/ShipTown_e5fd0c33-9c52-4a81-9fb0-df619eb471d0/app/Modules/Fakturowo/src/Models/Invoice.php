<?php

namespace App\Modules\Fakturowo\src\Models;

use App\BaseModel;
use Illuminate\Support\Carbon;

/**
 * App\Models\Invoice.
 *
 * @property int $id
 * @property int $order_id
 * @property string $fakturowo_invoice_id
 * @property string $fakturowo_invoice_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 */
class Invoice extends BaseModel
{
    protected $table = 'modules_fakturowo_invoices';

    /**
     * @var string[]
     */
    protected $fillable = [
        'order_id',
        'filename',
        'fakturowo_invoice_id',
        'fakturowo_invoice_url',
    ];
}
