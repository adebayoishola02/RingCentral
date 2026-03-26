<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRingCentralAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or policy check
    }

    public function rules(): array
    {
        return [
            'client_id'     => ['required', 'string', 'max:255'],
            'client_secret' => ['required', 'string', 'max:255'],
            'refresh_token' => ['required', 'string'],
            'access_token'  => ['required', 'string'],
            'phone_number'  => ['required', 'string'],
            'server_url'    => ['sometimes', 'url'],
            'extension_id'  => ['nullable', 'string'],
            'expires_at'  => ['nullable', 'string'],
            'friendly_name' => ['nullable', 'string', 'max:255'],
            'metadata'      => ['nullable', 'array'],
        ];
    }
}
