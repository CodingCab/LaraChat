<?php

namespace App\Http\Requests\PointOfSaleConfiguration;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'next_transaction_number' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
