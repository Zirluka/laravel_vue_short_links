<?php

namespace App\Http\Requests\Link;

use App\Rules\NotSelfDomain;
use App\Rules\ReachableUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LinkRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'link' => ['required', 'url:http,https', 'string', 'max:2048', new NotSelfDomain(), new ReachableUrl()],
            'password' => ['sometimes', 'nullable', 'string', 'min:4'],
            'expired_at' => ['sometimes', 'nullable', 'date', 'after:now']
        ];
    }
}
