<?php

namespace App\Http\Controllers\Api\Modules\ChatGPT;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Modules\ChatGpt\GenerateProductDescriptionRequest;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenAI\Laravel\Facades\OpenAI;

class GenerateProductDescriptionController extends Controller
{
    public function store(GenerateProductDescriptionRequest $request): JsonResource
    {
        $product = Product::find($request->product_id);
        $langCode = $request->language_code;

        $messages = [
            [
                'role' => 'system',
                'content' => "You are an AI copywriter specializing in e-commerce product descriptions. Your task is to create clear, engaging, and persuasive product descriptions in multiple languages",
            ],
            [
                'role' => 'user',
                'content' => "Write a short, engaging product description for '$product->name' in lang code '$langCode', highlighting key features and benefits for e-commerce. Max 255 characters.",
            ],
        ];

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => $messages,
        ]);

        return JsonResource::make([
            'description' => $result->choices[0]->message->content
        ]);
    }
}
