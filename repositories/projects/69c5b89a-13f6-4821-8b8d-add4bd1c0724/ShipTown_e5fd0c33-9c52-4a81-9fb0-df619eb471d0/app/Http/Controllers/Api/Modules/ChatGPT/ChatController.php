<?php

namespace App\Http\Controllers\Api\Modules\ChatGPT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenAI\Laravel\Facades\OpenAI;

class ChatController extends Controller
{
    public function store(Request $request): JsonResource
    {
        $data = $request->validate([
            'message' => 'required|string',
        ]);

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => $data['message']],
            ],
        ]);

        return JsonResource::make([
            'reply' => $result->choices[0]->message->content,
        ]);
    }
}
