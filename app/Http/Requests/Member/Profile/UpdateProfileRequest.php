<?php

namespace App\Http\Requests\Member\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\GenderEnum;

class UpdateProfileRequest extends FormRequest
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
            'nickname' => 'required|string|max:100',
            'birth_date' => 'nullable|date|before:today',
            'gender' => ['required', Rule::in(array_column(GenderEnum::cases(), "value"))],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nickname.required' => 'ニックネームは必須です。',
            'nickname.string' => '正しい形式のニックネームを入力ください。',
            'nickname.max' => 'ニックネームは100文字以内である必要があります。',
            'gender.in' => '正しい性別を選択してください。',
            'birth_date.date' => '正しい形式の生年月日を入力ください。',
            'birth_date.before' => '生年月日は今日以前の日付である必要があります。',
        ];
    }
}
