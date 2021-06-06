<?php


namespace App\Http\Requests\Ag;


class LoginRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'phone' => 'required|min:8',
            'pwd' => 'required|min:6'
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => '请输入手机号',
            'phone.min' => '手机号长度至少8位',
            'pwd.required' => '请输入登录密码',
            'pwd.min' => '登录密码长度至少6位',
        ];
    }

}
