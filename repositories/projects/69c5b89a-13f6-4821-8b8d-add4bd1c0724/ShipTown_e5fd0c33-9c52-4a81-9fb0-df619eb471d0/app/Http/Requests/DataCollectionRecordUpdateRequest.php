<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DataCollectionRecordUpdateRequest extends FormRequest
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
            'quantity_scanned' => ['sometimes', 'numeric'],
            'comment' => ['sometimes', 'string'],
            'unit_sold_price' => ['sometimes', 'numeric'],
            'price_source' => ['sometimes', 'string'],
            'price_source_id' => ['sometimes', 'numeric', 'nullable'],
        ];
    }
}
