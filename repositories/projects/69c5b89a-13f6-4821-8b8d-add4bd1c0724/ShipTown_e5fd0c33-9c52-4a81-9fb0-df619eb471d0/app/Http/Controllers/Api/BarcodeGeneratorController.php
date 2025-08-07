<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BarcodeGeneratorIndexRequest;
use Illuminate\Http\Response;
use Milon\Barcode\DNS2D;

class BarcodeGeneratorController extends Controller
{
    public function index(BarcodeGeneratorIndexRequest $request)
    {
        $content = $request->validated('content');
        $barcode_type = 'QRCODE';
        $width = 1;
        $height = 1;
        $color = $request->validated('color', 'black');
        $show_code = false;

        $content_type = 'image/svg+xml';
        $filename = 'barcode.svg';

        $barcode = new DNS2D;

        return new Response(
            $barcode->getBarcodeSVG($content, $barcode_type, $width, $height, $color, $show_code),
            200,
            [
                'Content-Type' => $content_type,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }
}
