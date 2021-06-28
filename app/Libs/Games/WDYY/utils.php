<?php

use Illuminate\Support\Facades\Crypt;

require_once 'config.php';

/**
 * 签名加密
 * @param $params
 * @return string
 */
function generateSign($params): string
{
    ksort($params);
    $string = [];
    foreach ($params as $key => $value) {
        $string[] = $key . '=' . $value;
    }
    $sign = implode('&', $string) . '&key=' . MD5_KEY;
    return strtolower(md5($sign));
}

function checkSign()
{
    $params = request()->post();
    $sign = $params['sign'];
    unset($params['sign']);
    if($sign != generateSign($params)){
        return false;
    }
    return $params;
}

function getToken()
{
    return request()->header('token');
}

function getUserId($token)
{
    $token = urldecode($token);
    $data = explode("+", Crypt::decrypt($token));
    return $data[0];
}


