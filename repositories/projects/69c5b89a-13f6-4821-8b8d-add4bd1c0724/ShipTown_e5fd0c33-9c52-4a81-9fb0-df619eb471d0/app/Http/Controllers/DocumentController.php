<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentIndexRequest;
use App\Models\DataCollection;
use App\Models\MailTemplate;
use App\Services\PdfService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(DocumentIndexRequest $request): StreamedResponse|string
    {
        /** @var MailTemplate $template */
        $template = MailTemplate::query()
            ->where('code', $request->validated('template_code'))
            ->first();

        $pdfString = PdfService::fromMustacheTemplate($template->html_template, [
            'data_collection' => DataCollection::query()->where('id', $request->validated('data_collection_id'))->first(),
        ]);

        return match ($request->validated('output_format', 'pdf')) {
            'pdf' => response()->stream(function () use ($pdfString) {
                echo $pdfString;
            }, '200', ['Content-Type' => 'application/pdf']),
            default => $pdfString,
        };
    }
}
