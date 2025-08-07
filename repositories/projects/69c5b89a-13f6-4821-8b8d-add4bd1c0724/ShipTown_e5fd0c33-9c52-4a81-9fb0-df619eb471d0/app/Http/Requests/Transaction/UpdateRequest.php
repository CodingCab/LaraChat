<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $transaction_id
 */
class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address_id' => ['sometimes', 'integer', 'exists:orders_addresses,id'],
            'billing_address_id' => ['sometimes', 'integer', 'exists:orders_addresses,id'],
        ];
    }
}
