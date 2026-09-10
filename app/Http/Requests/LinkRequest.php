<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LinkRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            "link" => "required|string|url"
        ];
    }
}
