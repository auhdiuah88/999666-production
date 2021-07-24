<?php


namespace App\Libs;


class Aes{

    public $key;

    public function __construct(){
        $key = env('AES_KEY','');
        $this->key = $key;
    }

    public function encrypt($data) {
        $data = openssl_encrypt($data, 'aes-128-ecb', base64_decode($this->key), OPENSSL_RAW_DATA);
        return base64_encode($data);
    }

    public function decrypt($data) {
        $encrypted = base64_decode($data);
        return openssl_decrypt($encrypted, 'aes-128-ecb', base64_decode($this->key), OPENSSL_RAW_DATA);
    }

    //解密
    public static function decryptno64($data, $key) {
        $encrypted = base64_decode($data);
        return openssl_decrypt($encrypted, 'aes-128-ecb', $key, OPENSSL_RAW_DATA);
    }

    //加密
    public static function encryptno64($data, $key) {
        $data =  openssl_encrypt($data, 'aes-128-ecb', $key, OPENSSL_RAW_DATA);
        return base64_encode($data);
    }

    public function encryptWithOpenssl($key, $data = '', $iv='')
    {
        return base64_encode(openssl_encrypt(json_encode($data), "AES-128-CBC", $key, OPENSSL_RAW_DATA,$iv));
    }

    public function decryptWithOpenssl($key, $data = '', $iv='')
    {
        return openssl_decrypt(base64_decode($data), "AES-128-CBC", $key, OPENSSL_RAW_DATA,$iv);
    }

}
