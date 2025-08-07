<?php

namespace App\Http\Controllers;

use App\Models\MailTemplate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MailTemplatePreviewController extends Controller
{
    public function index(Request $request, int $mailTemplate_id): Response|Application|ResponseFactory
    {
        /** @var MailTemplate $mailTemplate */
        $mailTemplate = MailTemplate::query()->findOrFail($mailTemplate_id);

        return response($mailTemplate->html_template);
    }
}
