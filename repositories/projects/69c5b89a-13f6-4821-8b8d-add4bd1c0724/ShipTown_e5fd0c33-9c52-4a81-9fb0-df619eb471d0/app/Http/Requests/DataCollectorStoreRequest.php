<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DataCollectorStoreRequest extends FormRequest
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
            'data_collection_id' => ['required', Rule::exists('data_collections', 'id')->whereNull('deleted_at')],
            'inventory_id' => ['required', 'exists:inventory,id'],
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['sometimes', 'exists:warehouses,id'],
            'warehouse_code' => ['required', 'exists:warehouses,code'],
            'quantity_requested' => ['sometimes', 'numeric'],
            'quantity_scanned' => ['sometimes', 'numeric'],
        ];
    }
}
