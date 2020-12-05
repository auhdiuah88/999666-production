<?php

namespace App\Http\Controllers;

use App\Models\Cx_User;
use App\Services\Pay\Leap;
use App\Services\Pay\PayStrategy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected static $merchantID = '';     // 商户ID
    protected static $secretkey = '';      // 密钥

    public function test2(Leap $leap) {
        self::$secretkey = env('PAY_SECRET_KEY');
//        return $leap->testGetCallbackUrl();
$json = '{
    "money": "104.000000",
    "pt_order": "CS202012025006904868391", 
    "sh_order": "202012051857228545788117",
    "time": "1606904958",
    "state": "4",
    "goods_desc": "recharge"
}';
       $params =  json_decode($json, true);
       $sign =  self::generateSign($params);
       $params['sign'] = $sign;
        return $params;
//        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if (Leap::generateSign($params) <> $sign) {
            return 'leap-签名错误';
        }
        return '通过';
    }

    public static function generateSign(array $params)
    {
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' . self::$secretkey;
//        dd($sign);
        return md5($sign);
    }
}
