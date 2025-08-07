<?php

namespace App\Modules\Magento2API\PriceSync\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagentoApiConnectionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'base_url' => 'required|url',
            'tag' => 'nullable',
            'magento_store_id' => 'nullable|numeric',
            'pricing_source_warehouse_id' => 'nullable|exists:warehouses,id',
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
            'api_access_token' => 'required|string',
            'access_token_secret' => 'required|string',
        ];
    }
}
