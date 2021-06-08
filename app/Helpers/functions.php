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
            case '>':
                $model = $model->where($key, '>', $item[1]);
                break;
            case '<':
                $model = $model->where($key, '<', $item[1]);
                break;
            case 'null':
                $model = $model->whereNull($key);
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

/**
 * @param $ip
 * @return bool
 * 检查ip是否在指定范围内
 */
function ipCheck($ip): bool
{
    $ip=myIp2long($ip);
    $ips = config('site.ips',[]);
    foreach($ips as $item)
    {
        $ban_range_low=myIp2long(trim($item[0])); //ip段首
        $ban_range_up=myIp2long(trim($item[1]));//ip段尾
        if ($ip>$ban_range_low && $ip<=$ban_range_up)
        {
            return true;
        }
    }
    return false;
}

function myIp2long($ip){
    $ip_arr = explode('.',$ip);
    $iplong = (16777216 * intval($ip_arr[0])) + (65536 * intval($ip_arr[1])) + (256 * intval($ip_arr[2])) + intval($ip_arr[3]);
    return $iplong;
}

/**
 * 获取真实IP
 * @return mixed|string
 */
function getIp(){
    $ip=request()->ip();
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        return is_ip($_SERVER['HTTP_CLIENT_IP'])?$_SERVER['HTTP_CLIENT_IP']:$ip;
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        return is_ip($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$ip;
    }else{
        return is_ip($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:$ip;
    }
}
function is_ip($str){
    $ip=explode('.',$str);
    for($i=0;$i<count($ip);$i++){
        if($ip[$i]>255){
            return false;
        }
    }
    return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/',$str);
}

/**
 * 返回字符串的毫秒数时间戳
 * @return mixed|string
 */
function get_total_millisecond()
{
    list($msec, $sec) = explode(' ', microtime());
    $msectime= (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000); //获取当前时间戳
    return (string)$msectime;
}

/**
 * 生成几位的随机数
 * @param int $n
 * @return int
 */
function randomStr(int $n): int
{
    if($n < 1)return 0;
    $a = $n;
    $str = '';
    $rootStr = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    for($i=$a;$i>=1;$i--)
    {
        shuffle($rootStr);
        $res = $rootStr[mt_rand(0,9)];
        if($a == $i && $res == '0'){
            $res = '9';
        }
        $str .= $res;
    }
    return (int)$str;
}

function dopost($url = '', $param = '', $headers)
{
    if (empty($url) || empty($param)) {
        return false;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function getPhoneReg()
{
    $phonePreg = [
        'india' => "/^\d{10}$/",
        'vn' => "/^\d{8,11}$/",
        'mx' => "/^\d{8,10}$/",
        'br' => "/^\d{8,11}$/",
    ];
    return $phonePreg[env('COUNTRY','india')];
}

function unicodeStrtoupper($a){
    $b = str_split($a, 1);
    $r = '';
    foreach($b as $v){
        $v = ord($v);
        if($v >= 97 && $v<= 122){
            $v -= 32;
        }
        $r .= chr($v);
    }
    return $r;
}
