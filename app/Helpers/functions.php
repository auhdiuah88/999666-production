<?php

function search_filter($str){
    $str = str_replace('*','',$str);
    $str = str_replace('%','',$str);
    $str = str_replace('_','',$str);
    return str_filter($str);
}

function str_filter($str){
    return addslashes(strip_tags(trim($str)));
}

function hide($str, $start, $len){
    return substr_replace($str,str_repeat("*",$len),$start,$len);
}

function day_start(){
    return strtotime(date('Y-m-d') . ' 00:00:00');
}

function day_end(){
    return strtotime(date('Y-m-d') . ' 23:59:59');
}

function makeInviteRelation($relation, $user_id){
    return '-' . trim($user_id . '-' . trim($relation,'-'),'-') . '-';
}

function makeModel($where, $model){
    foreach($where as $key => $item){
        switch ($item[0]){
            case '=':
                $model = $model->where($key, $item[1]);
                break;
            case 'BETWEEN':
                $model = $model->whereBetween($key, $item[1]);
                break;
            case 'in':
                $model = $model->whereIn($key, $item[1]);
                break;
            case 'IntegerInRaw':
                $model = $model->whereIntegerInRaw($key,$item[1]);
                break;
            case 'like':
                $model = $model->where($key, 'like', $item[1]);
                break;
        }
    }
    return $model;
}

function rpNBSP($html)
{
    return str_replace('&nbsp;',' ', $html);
}

function getHtml($html)
{
    return rpNBSP(htmlspecialchars_decode(htmlspecialchars_decode($html)));
}

function redisHGetALl($key, $arrKey=[]):array
{
    $data = \Illuminate\Support\Facades\Redis::hgetall($key);
    foreach($data as $k => &$val){
        if(in_array($k, $arrKey))$val = json_decode($val,true);
    }
    return $data;
}

function redisHSetAll($key, $array)
{
    foreach($array as $k => $arr){
        if(is_array($arr))$arr = json_encode($arr);
        $data[$k] = \Illuminate\Support\Facades\Redis::hset($key, $k, $arr);
    }
}

function channels():array
{
    $withdraw = array_keys(config('pay.withdraw'));
    $recharge = config('pay.recharge');
    return array_unique(array_merge($withdraw, $recharge));
}

function aesEncrypt($data)
{
    $hash = md5(env('VUE_AES_KEY','goshop6aes'));
    $salt = openssl_random_pseudo_bytes(8);
    $salted = '';
    $dx = '';
    while (strlen($salted) < 48) {
        $dx = md5($dx . $hash . $salt, true);
        $salted .= $dx;
    }
    $key = substr($salted, 0, 32);
    $iv = substr($salted, 32, 16);
    $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode('Salted__' . $salt . $encryptedData);
}

function aesDecrypt($data)
{
    $data = base64_decode($data);
    $hash = md5(env('VUE_AES_KEY','goshop6aes'));
    $cipherText = substr($data, 16);
    $salt = substr($data, 8, 8);
    $rounds = 3;
    $hashSalt = $hash . $salt;
    $md5Hash[] = md5($hashSalt, true);
    $result = $md5Hash[0];
    for ($i = 1; $i < $rounds; $i++) {
        $md5Hash[$i] = md5($md5Hash[$i - 1] . $hashSalt, true);
        $result .= $md5Hash[$i];
    }
    $key = substr($result, 0, 32);
    $iv = substr($result, 32, 16);
    return openssl_decrypt($cipherText, 'aes-256-cbc', $key, true, $iv);
}
