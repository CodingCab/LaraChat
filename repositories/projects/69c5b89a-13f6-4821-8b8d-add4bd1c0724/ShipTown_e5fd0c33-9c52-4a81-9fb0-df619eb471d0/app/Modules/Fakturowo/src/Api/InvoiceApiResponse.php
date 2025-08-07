<?php

namespace App\Modules\Fakturowo\src\Api;

/**
 * @property $invoiceId
 * @property $invoiceUrl
 */
class InvoiceApiResponse
{
    const int API_NUMBER_LINE = 1;
    const int PDF_URL_LINE = 2;
    const int PDF_FILENAME_LINE = 4;

    protected mixed $response;

    public mixed $invoiceId;
    public mixed $invoiceUrl;

    public mixed $filename;

    public function __construct($response)
    {
        $this->response = $response;
        $this->invoiceId = $this->response[self::API_NUMBER_LINE] ?? null;
        $this->invoiceUrl = $this->response[self::PDF_URL_LINE] ?? null;
        $this->filename = $this->response[self::PDF_FILENAME_LINE] ?? null;
    }
}
