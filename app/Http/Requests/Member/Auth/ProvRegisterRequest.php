<?php

namespace App\Http\Requests\Member\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ProvRegisterRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'email' => 'required|email|unique:users,email',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => 'メールアドレスの形式が不正です。',
            'email.unique' => 'このメールアドレスは既に使用されています。',
        ];
    }
}
