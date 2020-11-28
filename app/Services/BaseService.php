<?php


namespace App\Services;


use Illuminate\Support\Facades\Crypt;

abstract class BaseService
{
    public $_code = 200;

    public $_msg = "查询成功";

    public $_data = [];

    public function getUserId($token)
    {
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        return $data[0];
    }
}
