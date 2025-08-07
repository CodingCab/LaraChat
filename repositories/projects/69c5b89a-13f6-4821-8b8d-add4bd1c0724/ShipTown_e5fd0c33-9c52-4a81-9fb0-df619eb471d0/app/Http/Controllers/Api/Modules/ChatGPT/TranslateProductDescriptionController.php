<?php

namespace App\Http\Controllers\Api\Modules\ChatGPT;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Modules\ChatGpt\GenerateTranslateProductDescriptionRequest;
use App\Http\Resources\ProductDescriptionResource;
use App\Models\ProductDescription;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenAI\Laravel\Facades\OpenAI;

class TranslateProductDescriptionController extends Controller
{
    public function store(GenerateTranslateProductDescriptionRequest $request): JsonResource
    {
        $productDescription = ProductDescription::find($request->product_description_id);

        $translateTo = json_encode($request->auto_translate_to);

        $userMessage = "Please translate this description '$productDescription->description' from language code $productDescription->language_code to specific language code $translateTo";
        $userMessage .= "\n makesure the response is valid json with structure {'langcode': 'description'}";

        $messages = [
            [
                'role' => 'system',
                'content' => "You are an AI copywriter specializing in e-commerce product descriptions. Your task is to create clear, engaging, and persuasive product descriptions in multiple languages",
            ],
            [
                'role' => 'user',
                'content' => $userMessage,
            ],
        ];

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => $messages,
            'response_format' => ['type' => "json_object"],
        ]);

        $descriptions = json_decode($result->choices[0]->message->content, true);

        foreach ($descriptions as $languageCode => $description) {
            ProductDescription::updateOrCreate(
                ['product_id' => $productDescription->product_id, 'language_code' => $languageCode],
                ['description' => $description]
            );
        }

        $productDescriptions = ProductDescription::where('product_id', $productDescription->product_id)->get();

        return ProductDescriptionResource::collection($productDescriptions);
    }
}
