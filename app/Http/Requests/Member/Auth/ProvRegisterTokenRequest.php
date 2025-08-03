<?php

namespace App\Http\Requests\Member\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ProvRegisterTokenRequest extends FormRequest
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
            'token' => 'required|string|max:255',
            'email' => 'required|email',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'token.required' => 'トークンは必須です。',
            'token.string' => 'トークンの形式が不正です。',
            'token.max' => 'トークンは255文字以内で入力してください。',
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => 'メールアドレスの形式が不正です。',
        ];
    }
}
