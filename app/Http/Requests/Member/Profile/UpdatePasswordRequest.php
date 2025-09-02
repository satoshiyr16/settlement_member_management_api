<?php

namespace App\Http\Requests\Member\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
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
            'current_password' => 'required|string|min:8|max:32',
            'new_password' => 'required|string|min:8|max:32|confirmed',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'current_password.required' => '現在のパスワードは必須です。',
            'current_password.string' => '現在のパスワードの形式が不正です。',
            'current_password.min' => '現在のパスワードは8文字以上で入力してください。',
            'current_password.max' => '現在のパスワードは32文字以内で入力してください。',
            'new_password.required' => '新しいパスワードは必須です。',
            'new_password.string' => '新しいパスワードの形式が不正です。',
            'new_password.min' => '新しいパスワードは8文字以上で入力してください。',
            'new_password.max' => '新しいパスワードは32文字以内で入力してください。',
            'new_password.confirmed' => '新しいパスワードが一致しません。',
        ];
    }
}
