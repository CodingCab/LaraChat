<?php

namespace App\Http\Controllers\Api\Modules\Slack;

use App\Http\Controllers\Controller;
use App\Modules\Slack\src\Http\Requests\SlackConfigIndexRequest;
use App\Modules\Slack\src\Http\Requests\SlackConfigStoreRequest;
use App\Modules\Slack\src\Http\Resources\SlackConfigResource;
use App\Modules\Slack\src\Models\SlackConfig;

class ConfigController extends Controller
{
    public function index(SlackConfigIndexRequest $request): SlackConfigResource
    {
        $slackConfig = SlackConfig::query()->firstOrCreate();

        return SlackConfigResource::make($slackConfig);
    }

    public function store(SlackConfigStoreRequest $request): SlackConfigResource
    {
        $slackConfig = SlackConfig::query()->firstOrCreate();

        $slackConfig->update($request->validated());

        return SlackConfigResource::make($slackConfig->refresh());
    }
}
