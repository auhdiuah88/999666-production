<?php


namespace App\Libs\Games;


use App\Repositories\Api\UserRepository;

abstract class GameStrategy
{

    protected $UserRepository;

    public function __construct
    (
        UserRepository $userRepository
    )
    {
        $this->UserRepository = $userRepository;
    }

    abstract function launch($productId);

    abstract function userInfo();

    public function doRequest($url = '', $param = '', $headers)
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

    public function addUserBetting($user_id, $betting)
    {
        $this->UserRepository->addUserBetting($user_id, $betting);
    }

    //解密
    public static function decrypt($data, $key) {
        $encrypted = base64_decode($data);
        return openssl_decrypt($encrypted, 'aes-128-ecb', base64_decode($key), OPENSSL_RAW_DATA);
    }

    //加密
    public static function encrypt($data, $key) {
        $data =  openssl_encrypt($data, 'aes-128-ecb', base64_decode($key), OPENSSL_RAW_DATA);
        return base64_encode($data);
    }

    //get请求curl
    public function GetCurl($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 1);        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//设置获取的信息以文件流的形式返回，而不是直接输出
        $data = curl_exec($curl);                     //执行命令
        curl_close($curl);                            //关闭URL请求
        return  ($data);
    }
}
