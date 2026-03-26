<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendRingCentralSmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or add policy check
    }

    public function rules(): array
    {
        return [
            'account_uuid' => ['required', 'uuid', 'exists:ring_central_accounts,uuid'],
            'to'   => ['required', 'string'], // use custom phone validation if needed
            'text' => ['required', 'string', 'max:1600'],
            // 'from' => ['required', 'string'],
        ];
    }
}
