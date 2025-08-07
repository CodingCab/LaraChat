<?php

namespace App\Modules\ScheduledReport\src\Http\Requests;

use Cron\CronExpression;
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
            'name' => ['required', 'string', 'max:255'],
            'uri' => ['required', 'starts_with:/'],
//            'email' => ['required', 'email'], // this should be comma separeted list of emails
            'email' => [
                'required',
                function ($attribute, $value, $fail) {
                    $emails = explode(',', $value); // Split string by comma
                    foreach ($emails as $email) {
                        $email = trim($email); // Trim whitespace
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Validate email
                            $fail("Each email in the $attribute field must be a valid email address.");
                            return;
                        }
                    }
                },
            ],
            'cron' => [
                'required', 'string',
                function ($attribute, $value, $fail) {
                    if (!CronExpression::isValidExpression($value)) {
                        $fail("The $attribute field is not a valid cron expression.");
                    }
                },
            ]
        ];
    }
}
