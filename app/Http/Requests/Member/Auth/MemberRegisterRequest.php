<?php

namespace App\Http\Requests\Member\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\Common\GenderEnum;

class MemberRegisterRequest extends FormRequest
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
            'password' => 'required|string|min:8|max:32|confirmed',
            'nickname' => 'required|string|max:100',
            'gender' => ['nullable', Rule::in(array_column(GenderEnum::cases(), "value"))],
            'birth_date' => 'nullable|date|before:today',
            'enrollment_date' => 'required|date|before_or_equal:today',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => 'メールアドレスの形式が不正です。',
            'email.unique' => 'このメールアドレスは既に使用されています。',
            'password.required' => 'パスワードは必須です。',
            'password.string' => '正しい形式のパスワードを入力ください。',
            'password.min' => 'パスワードは8文字以上である必要があります。',
            'password.max' => 'パスワードは32文字以内である必要があります。',
            'password.confirmed' => 'パスワードが一致しません。',
            'nickname.required' => 'ニックネームは必須です。',
            'nickname.string' => '正しい形式のニックネームを入力ください。',
            'nickname.max' => 'ニックネームは100文字以内である必要があります。',
            'gender.in' => '正しい性別を選択してください。',
            'birth_date.date' => '正しい形式の生年月日を入力ください。',
            'birth_date.before' => '生年月日は今日以前の日付である必要があります。',
            'enrollment_date.required' => '入会日は必須です。',
            'enrollment_date.date' => '正しい形式の入会日を入力ください。',
            'enrollment_date.before_or_equal' => '入会日は今日以前の日付である必要があります。',
        ];
    }
}
