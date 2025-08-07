<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShippingLabelShowRequest;
use App\Models\ShippingLabel;

class ShippingLabelController extends Controller
{
    public function show(ShippingLabelShowRequest $request, ShippingLabel $shipping_label)
    {
        return match ($shipping_label->content_type) {
            ShippingLabel::CONTENT_TYPE_URL => redirect(base64_decode($shipping_label->base64_pdf_labels), 301),
            ShippingLabel::CONTENT_TYPE_PDF => response(base64_decode($shipping_label->base64_pdf_labels))
                ->header('Content-Disposition', 'attachment; filename = "'.$shipping_label->shipping_number.'.pdf"')
                ->header('Content-Type', 'application/pdf'),
            ShippingLabel::CONTENT_TYPE_RAW => response(base64_decode($shipping_label->base64_pdf_labels))
                ->header('Content-Disposition', 'attachment; filename = "'.$shipping_label->shipping_number.'"')
                ->header('Content-Type', 'application'),
            default => throw new \Exception('Unexpected match value'),
        };
    }
}
