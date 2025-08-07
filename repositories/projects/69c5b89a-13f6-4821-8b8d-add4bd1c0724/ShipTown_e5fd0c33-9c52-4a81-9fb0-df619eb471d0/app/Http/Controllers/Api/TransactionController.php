<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\PreviewReceiptRequest;
use App\Http\Requests\Transaction\PrintReceiptRequest;
use App\Http\Requests\Transaction\SendReceiptRequest;
use App\Http\Requests\Transaction\UpdateRequest;
use App\Http\Resources\DataCollectionResource;
use App\Mail\TransactionEmailReceiptMail;
use App\Mail\TransactionReceiptMail;
use App\Models\DataCollection;
use App\Models\MailTemplate;
use App\Models\Warehouse;
use App\Modules\DataCollector\src\Jobs\DispatchDataCollectorRecalculateRequestJob;
use App\Modules\PrintNode\src\Models\PrintJob;
use App\Modules\PrintNode\src\Resources\PrintJobResource;
use App\Services\PdfService;
use DNS1D;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TransactionController extends Controller
{
    public function update(UpdateRequest $request, int $transactionId): DataCollectionResource
    {
        $transaction = DataCollection::findOrFail($transactionId);

        $attributes = $request->validated();

        if (isset($attributes['billing_address_id']) && $attributes['billing_address_id'] !== $transaction->billing_address_id) {
            $transaction->update($attributes);

            $transaction->records->each(function ($record) {
                $record->update([
                    'recalculate_unit_tax' => 1,
                    'price_source' => null,
                    'price_source_id' => null,
                ]);
            });

            DispatchDataCollectorRecalculateRequestJob::dispatchAfterResponse($transaction);
        } else {
            $transaction->update($attributes);
        }

        return DataCollectionResource::make($transaction);
    }

    public function sendReceipt(SendReceiptRequest $request): bool
    {
        $transaction = DataCollection::findOrFail($request->validated('id'));

        /** @var MailTemplate $template */
        $template = MailTemplate::query()
            ->where('code', 'transaction_email_receipt')
            ->where('mailable', TransactionEmailReceiptMail::class)
            ->first();

        $products = $this->getProducts($transaction);

        $email = new TransactionEmailReceiptMail($template, [
            'transaction' => [
                'id' => $transaction->id,
                'subtotal' => $transaction->total_sold_price,
                'total' => $transaction->total_sold_price,
                'total_tax' => $transaction->total_tax,
                'shipping' => 0,
                'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
            ],
            'shipping_address' => $transaction->shippingAddress->toArray(),
            'billing_address' => $transaction->billingAddress->toArray(),
            'products' => $products,
        ]);

        Mail::to($transaction->shippingAddress->email)->send($email);

        return true;
    }

    public function previewReceipt(PreviewReceiptRequest $request): string
    {
        /** @var DataCollection $transaction */
        $transaction = DataCollection::findOrFail($request->validated('id'));

        $template = $this->prepareTransactionForReceipt($transaction);

        return $this->parseReceiptTemplateForPreview($template, $transaction);
    }

    public function printReceipt(PrintReceiptRequest $request): PrintJobResource
    {
        /** @var DataCollection $transaction */
        $transaction = DataCollection::findOrFail($request->validated('id'));

        $html = $this->prepareTransactionForReceipt($transaction);

        $template = $this->parseReceiptTemplateForPrint($html, $transaction);

        if (!$transaction->receipt_printed) {
            $transaction->update(['receipt_printed' => true]);
        }

        $printJob = new PrintJob;
        $printJob->printer_id = $request->printer_id;
        $printJob->title = "transaction $transaction->id receipt";
        $printJob->content = base64_encode($template);
        $printJob->content_type = 'raw_base64';
        $printJob->save();

        return PrintJobResource::make($printJob);
    }

    private function prepareTransactionForReceipt(DataCollection $transaction): string
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::find($user->warehouse_id);

        /** @var MailTemplate $template */
        $template = MailTemplate::query()
            ->where('code', 'transaction_receipt')
            ->where('mailable', TransactionReceiptMail::class)
            ->first();

        $products = $this->getProducts($transaction);


        if ($transaction->total_paid === null || $transaction->total_paid == 0) {
            $changeDue = 0;
        } else {
            $changeDue = $transaction->total_outstanding === null ? $transaction->total_sold_price - $transaction->total_paid : $transaction->total_outstanding;
        }

        return PdfService::fromMustacheTemplate(
            $template->text_template,
            [
                'transaction' => [
                    'id' => $transaction->id,
                    'discount' => $transaction->total_discount ?? 0,
                    'total' => $transaction->total_sold_price ?? 0,
                    'shipping' => 0,
                    'tax' => 0,
                    'seller' => $user->id,
                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'paid' => $transaction->total_paid ?? 0,
                    'change' => $changeDue,
                ],
                'store' => [
                    'name' => $warehouse->name,
                    'address' => $warehouse->address,
                ],
                'products' => $products,
            ],
            true
        );
    }

    private function getProducts(DataCollection $transaction): array
    {
        return $transaction->records->map(function ($record) {
            $product = $record->product;

            return [
                'sku' => $product->sku,
                'name' => $product->name,
                'quantity' => $record->quantity_scanned,
                'price' => $record->unit_sold_price,
                'total' => $record->total_sold_price,
            ];
        })->toArray();
    }

    private function parseReceiptTemplateForPreview(string $template, DataCollection $transaction): string
    {
        $template = str_replace("</esc-br>", "", $template);
        $template = str_replace("</esc-dashed-line>", "", $template);

        $barcode = DNS1D::getBarcodeSVG($transaction->id, 'C39', 2, 100, 'black', true);

        if (!$transaction->receipt_printed) {
            $template = str_replace("<esc-font-big>DUPLICATE</esc-font-big>", "", $template);
            $template = str_replace("DUPLICATE", "", $template);
        }

        $tagsWithHtml = [
            'left' => '<div class="left">',
            'center' => '<div class="center">',
            'right' => '<div class="right">',
            'bold' => '<span class="bold">',
            'bold-off' => '',
            'br' => '<br>',
            'font-large' => '<span class="font-large">',
            'font-big' => '<span class="font-big">',
            'font-normal' => '<span class="font-normal">',
            'dashed-line' => '<div class="dashed-line">-------------------------</div>',
        ];

        $tagsWithNonEmptyEndHtml = [
            'center' => '</div>',
            'right' => '</div>',
            'left' => '</div>',
            'bold-off' => '</span>',
            'font-large' => '</span>',
            'font-big' => '</span>',
            'font-normal' => '</span>',
        ];

        foreach ($tagsWithHtml as $tag => $html) {
            $template = str_replace("<esc-$tag>", $html, $template);
            if (isset($tagsWithNonEmptyEndHtml[$tag])) {
                $template = str_replace("</esc-$tag>", $tagsWithNonEmptyEndHtml[$tag], $template);
            }
        }

        $template = str_replace("<div class=\"left\"><esc-table-wrap>", "<table><thead>", $template);
        $template = str_replace("</esc-table-wrap></div>", "</tbody></table>", $template);

        while (str_contains($template, '<esc-table>')) {
            $tableStart = strpos($template, '<esc-table>');
            $tableEnd = strpos($template, '</esc-table>', $tableStart);
            $tableContent = substr($template, $tableStart, $tableEnd - $tableStart + 12);

            $columns = [];
            preg_match_all('/<esc-column[^>]*>(.*?)<\/esc-column>/', $tableContent, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $columns[] = [
                    'content' => $match[1],
                    'attributes' => $this->parseAttributes($match[0]),
                ];
            }

            static $isFirstTable = true;
            if ($isFirstTable) {
                $parsedTable = '<tr>';
                foreach ($columns as $column) {
                    $parsedTable .= '<th>' . $column['content'] . '</th>';
                }
                $parsedTable .= '</tr></thead><tbody>';
                $isFirstTable = false;
            } else {
                $parsedTable = '<tr>';
                foreach ($columns as $key => $column) {
                    $parsedTable .= '<td>' . $column['content'] . '</td>';
                }
                $parsedTable .= '</tr>';
            }

            $template = str_replace($tableContent, $parsedTable, $template);
        }

        $template = str_replace("<div class=\"left\"><esc-table remove-in-preview>", "<esc-table remove-in-preview>", $template);
        $template = str_replace("</esc-table></div>", "</esc-table>", $template);

        while (str_contains($template, '<esc-table remove-in-preview>')) {
            $tableStart = strpos($template, '<esc-table remove-in-preview>');
            $tableEnd = strpos($template, '</esc-table>', $tableStart);
            $tableContent = substr($template, $tableStart, $tableEnd - $tableStart + 12);

            $columns = [];
            preg_match_all('/<esc-column[^>]*>(.*?)<\/esc-column>/', $tableContent, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $columns[] = [
                    'content' => $match[1],
                    'attributes' => $this->parseAttributes($match[0]),
                ];
            }

            $parsedTable = '<div class="text-right summary">';
            foreach ($columns as $column) {
                $parsedTable .= '<span>' . $column['content'] . '</span>';
            }
            $parsedTable .= '</div>';

            $template = str_replace($tableContent, $parsedTable, $template);
        }

        while (str_contains($template, '<esc-link>')) {
            $linkStart = strpos($template, '<esc-link>');
            $linkEnd = strpos($template, '</esc-link>', $linkStart);
            $linkContent = substr($template, $linkStart, $linkEnd - $linkStart + 11);

            preg_match('/<esc-link>(.*?)<\/esc-link>/', $linkContent, $match);
            $linkHref = $match[1];

            $parsedLink = '<a href="' . $linkHref . '" target="_blank">' . $linkHref . '</a>';

            $template = str_replace($linkContent, $parsedLink, $template);
        }

        $template = preg_replace('/(<span[^>]*>)\n+/', '$1', $template);
        $template = preg_replace('/(<div[^>]*>)\n+/', '$1', $template);
        $template = preg_replace('/^\n+|\n+$/', '', $template);
        $template = str_replace("\n", "<br>", $template);

        if ($barcode) {
            $template = $barcode . $template . $barcode;
        }

        return $template;
    }

    private function parseReceiptTemplateForPrint(string $template, DataCollection $transaction): string
    {
        $esc = chr(27);

        $template = str_replace("<esc-table-wrap>", "", $template);
        $template = str_replace("</esc-table-wrap>", "", $template);
        $template = str_replace("<esc-link>", "", $template);
        $template = str_replace("</esc-link>", "", $template);

        if (!$transaction->receipt_printed) {
            $template = str_replace("<esc-font-big>DUPLICATE</esc-font-big>", "", $template);
            $template = str_replace("DUPLICATE", "", $template);
        }

        $codes = [
            'left' => $esc . 'a' . chr(0),
            'center' => $esc . 'a' . chr(1),
            'right' => $esc . 'a' . chr(2),
            'font-large' => chr(29) . '!' . chr(68),
            'font-big' => chr(29) . '!' . chr(17),
            'font-normal' => chr(29) . '!' . chr(0),
            'br' => chr(10),
            'cut' => chr(29) . 'V' . chr(1),
            'bold' => $esc . 'E' . chr(1),
            'bold-off' => $esc . 'E' . chr(0),
            'tab' => chr(9),
            'dashed-line' => $esc . 'a' . chr(1) . '-------------------------' . $esc . 'a' . chr(0),
            'barcode' => $esc . 'h' . chr(50) . $esc . 'H' . chr(2) . $esc . 'f' . chr(1) . $esc . 'k' . chr(4) . "$transaction->id" . chr(0),
        ];

        $tagsWithCodes = [
            'left' => $codes['left'],
            'center' => $codes['center'],
            'right' => $codes['right'],
            'bold' => $codes['bold'],
            'bold-off' => $codes['bold-off'],
            'br' => $codes['br'],
            'cut' => $codes['cut'],
            'font-large' => $codes['font-large'],
            'font-big' => $codes['font-big'],
            'font-normal' => $codes['font-normal'],
            'tab' => $codes['tab'],
            'dashed-line' => $codes['dashed-line'],
            'barcode' => $codes['barcode'],
        ];

        $tagsWithNonEmptyEnd = [
            'center' => $codes['left'],
            'right' => $codes['left'],
            'bold' => $codes['bold-off'],
            'font-large' => $codes['font-normal'],
            'font-big' => $codes['font-normal'],
        ];

        foreach ($tagsWithCodes as $tag => $code) {
            $template = str_replace("<esc-$tag>", $code, $template);
            if (isset($tagsWithNonEmptyEnd[$tag])) {
                $template = str_replace("</esc-$tag>", $tagsWithNonEmptyEnd[$tag], $template);
            } else {
                $template = str_replace("</esc-$tag>", '', $template);
            }
        }

        while (str_contains($template, '<esc-table>')) {
            $tableStart = strpos($template, '<esc-table>');
            $tableEnd = strpos($template, '</esc-table>', $tableStart);
            $tableContent = substr($template, $tableStart, $tableEnd - $tableStart + 12);

            $columns = [];
            preg_match_all('/<esc-column[^>]*>(.*?)<\/esc-column>/', $tableContent, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $columns[] = [
                    'content' => $match[1],
                    'attributes' => $this->parseAttributes($match[0]),
                ];
            }

            $parsedTable = $this->columnify($columns);
            $template = str_replace($tableContent, $parsedTable, $template);
        }

        while (str_contains($template, '<esc-table remove-in-preview>')) {
            $tableStart = strpos($template, '<esc-table remove-in-preview>');
            $tableEnd = strpos($template, '</esc-table>', $tableStart);
            $tableContent = substr($template, $tableStart, $tableEnd - $tableStart + 12);

            $columns = [];
            preg_match_all('/<esc-column[^>]*>(.*?)<\/esc-column>/', $tableContent, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $columns[] = [
                    'content' => $match[1],
                    'attributes' => $this->parseAttributes($match[0]),
                ];
            }

            $parsedTable = $this->columnify($columns);
            $template = str_replace($tableContent, $parsedTable, $template);
        }

        $parsedTemplate = $esc . '@';
        $parsedTemplate .= $template;
        $parsedTemplate .= $esc . 'd' . chr(5);
        $parsedTemplate .= $esc . 'i';
        $pinValue = 0 + 48; // Character '0' or '1'.
        $onValue = intdiv(120, 2);
        $offValue = intdiv(240, 2);
        $parsedTemplate .= $esc . 'p' . chr($pinValue) . chr($onValue) . chr($offValue);

        ray($parsedTemplate);

        return $parsedTemplate;
    }

    private function parseAttributes(string $tag): array
    {
        $attributes = [];
        preg_match_all('/(\w+)="([^"]*)"/', $tag, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $attributes[$match[1]] = $match[2];
        }

        return $attributes;
    }

    private function columnify(array $columns, int $space = 2): string
    {
        $wrapped = [];
        $lines = [];
        $totalColumns = count($columns);

        foreach ($columns as $index => $column) {
            $colWidth = $column['attributes']['width'];
            if ($index === 0 || $index === $totalColumns - 1) {
                $colWidth -= $space / 2;
            } else {
                $colWidth -= $space;
            }
            $wrapped[$index] = wordwrap($column['content'], $colWidth, "\n", true);
            $lines[$index] = explode("\n", $wrapped[$index]);
        }

        $maxLines = max(array_map('count', $lines));
        $allLines = [];

        for ($i = 0; $i < $maxLines; $i++) {
            $line = '';
            foreach ($columns as $index => $column) {
                $colWidth = $column['attributes']['width'];
                if ($index === 0 || $index === $totalColumns - 1) {
                    $colWidth -= $space / 2;
                } else {
                    $colWidth -= $space;
                }
                $text = $lines[$index][$i] ?? '';
                if (isset($column['attributes']['align']) && $column['attributes']['align'] === 'right') {
                    $line .= str_pad($text, $colWidth, ' ', STR_PAD_LEFT);
                } else {
                    $line .= str_pad($text, $colWidth);
                }
                if ($index < $totalColumns - 1) {
                    $line .= str_repeat(' ', $space);
                }
            }
            $allLines[] = $line;
        }

        return implode("\n", $allLines) . "\n";
    }
}
