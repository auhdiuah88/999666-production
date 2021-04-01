<?php


namespace App\Services\Message;


use Illuminate\Support\Facades\Http;

class IndiaMessage extends MessageStrategy
{

    private $desc = "印度短信";

    function sendRegisterCode($phone): array
    {
        $url = "http://sms.skylinelabs.cc:20003/sendsmsV2";
        $phone = "91" . $phone;
        $account = "cs_aheln9";
        $sign = md5($account . "u2AGYncI" . date("YmdHis"));
        $code = mt_rand(100000, 999999);
        $context = urlencode("[sky-shop] SMS verification code is{$code}, valid for 5 minutes, please don't tell others.");
        $params = [
            "account" => $account,
            "sign" => $sign,
            "numbers" => $phone,
            "content" => $context,
            "datetime" => date("YmdHis")
        ];
        $result = Http::post($url, $params)->json();
        if ($result["status"] == 0) {
            return ["code" => 200, "obj" => $code];
        }

        return ["code" => 402];
    }
}
