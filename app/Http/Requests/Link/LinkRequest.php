<?php

namespace App\Http\Requests\Link;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LinkRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'link' => ['required', 'url'],
            'password' => ['sometimes', 'nullable', 'string', 'min:4'],
            'expired_at' => ['sometimes', 'nullable', 'date', 'after:now']
        ];
    }
}
