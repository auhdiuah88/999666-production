<?php


namespace App\Services\Message;


use App\Services\RequestService;

abstract class MessageStrategy
{

    public $_code = 200;
    public $_msg = "success";
    public $_data = [];

    protected $requestService;

    public function __construct
    (
        RequestService $requestService
    )
    {
        $this->requestService = $requestService;
    }

    protected $registerMessage = "[sky-shop] SMS verification code is%s, valid for 5 minutes, please don't tell others. ";
//    protected $registerMessage = "【sky-shop】Your verification code is %s";

    abstract function sendRegisterCode($phone);

    protected function makeCode(): int
    {
        return mt_rand(100000, 999999);
    }

    protected function makeContent($content, $code): string
    {
        return urlencode(sprintf($content, $code));
    }

    protected function setError($code, $error): bool
    {
        $this->_code = $code;
        $this->_msg = $error;
        return false;
    }

}
