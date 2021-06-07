<?php


namespace App\Http\Requests\Ag;


class LoginRequest extends BaseRequest
{

    protected $rule = [
        'login' => [
            'phone' => 'required|min:8',
            'pwd' => 'required|min:6'
        ],
    ];

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->message = [
            'login' => [
                'phone.required' => trans('ag.login_request1'),
                'phone.min' => trans('ag.login_request2'),
                'pwd.required' => trans('ag.login_request3'),
                'pwd.min' => trans('ag.login_request4'),
            ]
        ];
    }

}
