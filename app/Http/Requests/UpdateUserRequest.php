<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "email" => ["sometimes", "string", "email", Rule::unique('users')->ignore($this->user()->id), "max:255", "lowercase"],
            "name" => ["sometimes", "string", "max:255"],
            "password" => ["sometimes", "string", Rules\Password::defaults(), "max:255"]
        ];
    }
}
