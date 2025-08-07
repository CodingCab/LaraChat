<?php

namespace App\Http\Controllers\Admin\Settings\Modules\Webhooks;

use App\Http\Controllers\Controller;
use App\Modules\Webhooks\src\Http\Requests\WebhookSubscriptionIndexRequest;
use App\Modules\Webhooks\src\WebhooksServiceProviderBase;

class SubscriptionController extends Controller
{
    public function index(WebhookSubscriptionIndexRequest $request)
    {
        if (WebhooksServiceProviderBase::isEnabled()) {
            return view('webhooks::WebhooksSubscriptionsPage');
        }

        return back()->with(['alert-warning-message' => 'Module disabled, please enable first']);
    }
}
