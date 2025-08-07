<?php

namespace App\Modules\Automations\src\Actions\Order;

use App\Modules\Automations\src\Abstracts\BaseOrderActionAbstract;
use App\Modules\PrintNode\src\Models\PrintJob;
use App\Services\OrderService;

class PrintAddressLabelAction extends BaseOrderActionAbstract
{
    public function handle(string $options = ''): bool
    {
        $printerId = $options;
        $template = 'address_label';

        if (str_contains($options, ',')) {
            [$printerId, $template] = array_pad(explode(',', $options, 2), 2, 'address_label');
        }

        $pdfString = OrderService::getOrderPdf($this->order->order_number, $template);

        PrintJob::query()->create([
            'printer_id' => $printerId,
            'content_type' => PrintJob::PDF_BASE64,
            'content' => base64_encode($pdfString),
            'title' => 'Address Label for Order '.$this->order->order_number,
            'source' => $template,
        ]);

        return true;
    }
}
