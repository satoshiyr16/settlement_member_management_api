<?php

namespace App\Http\Requests\Member\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginMemberRequest extends FormRequest
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
            'email' => 'required|email',
            'password' => 'required|string|min:8|max:32',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => 'メールアドレスの形式が不正です。',
            'password.required' => 'パスワードは必須です。',
            'password.string' => 'パスワードの形式が不正です。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.max' => 'パスワードは32文字以内で入力してください。',
        ];
    }
}
