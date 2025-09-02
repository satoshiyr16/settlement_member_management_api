<?php

namespace App\Http\Requests\Member\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailRequest extends FormRequest
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
            'current_email' => 'required|email',
            'new_email' => 'required|email|unique:users,email',
            'new_email_confirmation' => 'required|email|same:new_email',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'current_email.required' => '現在のメールアドレスは必須です。',
            'current_email.email' => '現在のメールアドレスの形式が不正です。',
            'new_email.required' => '新しいメールアドレスは必須です。',
            'new_email.email' => '新しいメールアドレスの形式が不正です。',
            'new_email_confirmation.required' => '新しいメールアドレスの確認は必須です。',
            'new_email_confirmation.email' => '新しいメールアドレスの確認の形式が不正です。',
            'new_email_confirmation.same' => '新しいメールアドレスの確認が一致しません。',
        ];
    }
}
