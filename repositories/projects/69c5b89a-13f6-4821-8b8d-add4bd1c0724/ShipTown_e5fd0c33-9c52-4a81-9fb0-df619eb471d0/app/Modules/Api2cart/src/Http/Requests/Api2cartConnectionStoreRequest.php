<?php

namespace App\Modules\Api2cart\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Api2cartConnectionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'store_url' => 'required|url',
            'cart_id' => 'required|in:Magento2Api,Shopify',
            'verify' => 'sometimes|boolean',

            "magento_consumer_key" => 'sometimes|string',
            "magento_consumer_secret" => 'sometimes|string',
            "magento_access_token" => 'sometimes|string',
            "magento_token_secret" => 'sometimes|string',

            "shopify_access_token" => "sometimes|string",
            "shopify_api_key" => "sometimes|string",
            "shopify_api_password" => "sometimes|string",
//            "shopify_shared_secret" => "sometimes|string",
        ];
    }
}
