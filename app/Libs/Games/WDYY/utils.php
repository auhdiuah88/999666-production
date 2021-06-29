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
    $sign = implode('&', $string) . '&key=' . env('WDYY_KEY', 'QDAQpubHyfu7tX4');
    return strtolower(md5($sign));
}

function checkSign()
{
    $params = request()->post();
    $sign = $params['sign'];
    unset($params['sign']);
//    if($sign != generateSign($params)){
//        return false;
//    }
    return $params;
}

function getToken()
{
    $token = request()->header('token');
    $user_id = getUserId($token);
    return Crypt::encrypt($user_id);
}

function getUserId($token)
{
    $token = urldecode($token);
    $data = explode("+", Crypt::decrypt($token));
    return $data[0];
}

function getUserIdFromToken($token)
{
    return Crypt::decrypt($token);
}


