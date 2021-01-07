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

}
