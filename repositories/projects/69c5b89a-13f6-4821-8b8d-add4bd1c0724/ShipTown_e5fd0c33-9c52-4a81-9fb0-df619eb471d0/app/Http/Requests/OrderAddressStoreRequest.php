<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class OrderAddressStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'gender' => 'string|nullable',
            'address1' => 'string|nullable',
            'address2' => 'string|nullable',
            'postcode' => 'string|nullable',
            'city' => 'string|nullable',
            'country_code' => 'string|nullable',
            'country_name' => 'string|nullable',
            'company' => 'string|nullable',
            'email' => 'email|nullable',
            'phone' => 'string|nullable',
            'document_type' => 'string|nullable',
            'document_number' => 'string|nullable',
            'tax_id' => 'string|nullable',
            'discount_code' => [
                'string',
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && ! DB::table('modules_data_collector_discounts')->where('code', $value)->exists()) {
                        $fail('The selected discount code is invalid.');
                    }
                },
            ],
            'tax_exempt' => 'boolean|sometimes',
        ];
    }
}
