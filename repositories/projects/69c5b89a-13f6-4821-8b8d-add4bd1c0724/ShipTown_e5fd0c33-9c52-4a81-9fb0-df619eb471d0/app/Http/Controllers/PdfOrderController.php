<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;

/**
 * Class AddressLabel.
 */
class PdfOrderController extends Controller
{
    public function show(Request $request, string $order_number, string $template)
    {
        $pdfString = OrderService::getOrderPdf($order_number, $template);

        $this->throwPdfResponse($pdfString);
    }
}
