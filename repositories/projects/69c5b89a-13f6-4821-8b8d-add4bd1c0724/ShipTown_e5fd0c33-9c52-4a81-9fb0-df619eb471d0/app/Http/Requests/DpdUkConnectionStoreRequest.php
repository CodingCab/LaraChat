<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DpdUkConnectionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_number' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'collection_address' => 'required|array',
            'collection_address.company' => 'required|string',
            'collection_address.full_name' => 'sometimes|string',
            'collection_address.first_name' => 'sometimes|string',
            'collection_address.last_name' => 'sometimes|string',
            'collection_address.email' => 'required|string',
            'collection_address.address1' => 'required|string',
            'collection_address.address2' => 'required|string',
            'collection_address.postcode' => 'required|string',
            'collection_address.city' => 'required|string',
            'collection_address.state_code' => 'sometimes|string',
            'collection_address.state_name' => 'sometimes|string',
            'collection_address.country_code' => 'sometimes|string',
            'collection_address.country_name' => 'sometimes|string',
            'collection_address.phone' => 'required|string',
            'collection_address.fax' => 'sometimes|string',
            'collection_address.website' => 'sometimes|string',
            'collection_address.region' => 'sometimes|string',
        ];
    }
}
