<?php

namespace App\Modules\Fakturowo\src\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\Fakturowo\src\Models\Invoice;
use App\Modules\Reports\src\Models\Report;

class FakturowoInvoicesReport extends Controller
{
    public function index()
    {
        $report = new Report();
        $report->baseQuery = Invoice::query()
            ->leftJoin('orders', 'orders.id', '=', 'modules_fakturowo_invoices.order_id');

        $report->defaultSort = '-modules_fakturowo_invoices.id';

        $report->addField('created_at', 'modules_fakturowo_invoices.created_at', 'datetime', hidden: false);
        $report->addField('id', 'modules_fakturowo_invoices.id', 'numeric');
        $report->addField('order_number', 'orders.order_number', hidden: false);
        $report->addField('filename', 'modules_fakturowo_invoices.filename', hidden: false);
        $report->addField('fakturowo_invoice_url', 'modules_fakturowo_invoices.fakturowo_invoice_url', 'url', hidden: false);
        $report->addField('fakturowo_invoice_id', 'modules_fakturowo_invoices.fakturowo_invoice_id');

        return $report->response();
    }
}
