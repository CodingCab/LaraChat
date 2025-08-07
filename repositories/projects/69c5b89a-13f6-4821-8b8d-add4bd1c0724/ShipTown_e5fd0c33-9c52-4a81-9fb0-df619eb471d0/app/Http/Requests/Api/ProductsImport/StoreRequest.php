<?php

namespace App\Http\Requests\Api\ProductsImport;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'mappedFields' => 'required|json',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => t('Please upload a CSV file'),
            'file.file' => t('The uploaded file is invalid'),
            'file.mimes' => t('The file must be a CSV file'),
            'file.max' => t('The file size must not exceed 10MB'),
        ];
    }
}
