<?php


namespace App\Http\Requests\Ag;


class UserRequest extends BaseRequest
{

    protected $rule = [
        'add_link' => [
            'rate' => 'required|numeric|gte:0.1|lte:10',
            'user_type' => 'required|integer'
        ],
        'del_link' => [
            'id' => 'required|integer|gte:1'
        ],
    ];

    protected $message = [
        'add_link' => [
            'rate.required' => '请输入返点',
            'rate.number' => '返点需要是数字',
            'rate.gte' => '返点需要大于0.1',
            'rate.lt' => '返点需要小于等于10',
            'user_type.required' => '请选择开户类型',
            'user_type.integer' => '开户类型需要是整数'
        ],
        'del_link' => [
            'id.required' => '请选择链接',
        ],
    ];

}
