<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderAddressUpdateRequest extends FormRequest
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
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'string|max:255|nullable',
            'address1' => 'required|string|max:255',
            'address2' => 'string|max:255|nullable',
            'postcode' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country_code' => 'required|string|min:2|max:3',
            'country_name' => 'required|string|max:255',
            'fax' => 'string|max:255|nullable',
            'region' => 'string|max:255|nullable',
            'tax_exempt' => 'sometimes|boolean',
            'state_code' => 'string|max:255|nullable',
            'state_name' => 'string|max:255|nullable',
            'website' => 'url|max:255|nullable',
            'discount_code' => 'string|nullable',
            'locker_box_code' => 'string|max:255|nullable',
        ];
    }
}
