<?php


namespace App\Http\Requests\Ag;


class UserRequest extends BaseRequest
{

    protected $rule = [
        'add_link' => [
            'rate' => 'required|numeric|gte:0.1|lte:8.5',
            'user_type' => 'required|integer'
        ],
        'del_link' => [
            'id' => 'required|integer|gte:1'
        ],
    ];

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->message = [
            'add_link' => [
                'rate.required' => trans('ag.user_request1'),
                'rate.number' => trans('ag.user_request2'),
                'rate.gte' => trans('ag.user_request3'),
                'rate.lte' => trans('ag.user_request4'),
                'user_type.required' => trans('ag.user_request5'),
                'user_type.integer' => trans('ag.user_request6')
            ],
            'del_link' => [
                'id.required' => trans('ag.user_request7'),
            ],
        ];
    }

}
