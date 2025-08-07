<?php

namespace App\Modules\PrintNode\src\Listeners;

use App\Events\ShippingLabel\ShippingLabelCreatedEvent;
use App\Models\ShippingLabel;
use App\Modules\PrintNode\src\Models\PrintJob;
use Exception;

class ShippingLabelCreatedListener
{
    /**
     * @throws Exception
     */
    public function handle(ShippingLabelCreatedEvent $event): void
    {
        $label = $event->shippingLabel;

        $printer_id = data_get($label, 'user.printers.shipping_labels_4x6', auth()->user()->printers['shipping_labels_4x6'] ?? null);

        if (!$printer_id) {
            return;
        }

        match ($label->content_type) {
            ShippingLabel::CONTENT_TYPE_URL => $this->printFromUrl($label, $printer_id),
            ShippingLabel::CONTENT_TYPE_PDF => $this->createPdfPrintJob($label, $printer_id),
            ShippingLabel::CONTENT_TYPE_RAW => $this->createRawPrintJob($label, $printer_id),
            default => throw new Exception('Unexpected match value'),
        };
    }

    public function printFromUrl(ShippingLabel $label, mixed $printer_id): PrintJob
    {
        $printJob = new PrintJob;
        $printJob->title = 'Url Print';
        $printJob->pdf_url = base64_decode($label->base64_pdf_labels);
        $printJob->printer_id = $printer_id;
        $printJob->save();

        return $printJob;
    }

    public function createPdfPrintJob(ShippingLabel $label, mixed $printer_id): PrintJob
    {
        $printJob = new PrintJob;
        $printJob->title = 'Url Print';
        $printJob->content_type = 'pdf_base64';
        $printJob->content = $label->base64_pdf_labels;
        $printJob->printer_id = $printer_id;
        $printJob->save();

        return $printJob;
    }

    public function createRawPrintJob(ShippingLabel $label, mixed $printer_id): PrintJob
    {
        $printJob = new PrintJob;
        $printJob->title = 'Raw Print';
        $printJob->content_type = 'raw_base64';
        $printJob->content = $label->base64_pdf_labels;
        $printJob->printer_id = $printer_id;
        $printJob->save();

        return $printJob;
    }
}
