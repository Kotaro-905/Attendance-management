<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
{
    return [
        'email' => ['required', 'email'],
        'password' => ['required'],
    ];
}

public function messages(): array
{
    return [
        'email.required' => 'メールアドレスを入力してください',
        'password.required' => 'パスワードを入力してください',
    ];
}
}
