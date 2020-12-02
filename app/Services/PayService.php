<?php


namespace App\Services;


use Illuminate\Support\Facades\Crypt;

/**
 *
 */
class PayService extends BaseService
{
//    protected static $url = 'http://ipay-in.yynn.me';
    protected static $url = 'http://payqqqbank.payto89.com';  // 支付网关

    protected static $url_cashout = 'http://tqqqbank.payto89.com:82'; // 提现网关

    // 正式环境
    protected static $merchantID = 262593573;
    protected static $secretkey = '4e70f59ec59149a6b81d26aafed8f6fb';

    /**
     * 生成签名   sign = Md5(key1=vaIue1&key2=vaIue2&key=签名密钥);
     */
    public static function generateSign(array $params)
    {
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' . self::$secretkey;
        return md5($sign);
    }

    /**
     * 生成订单号
     */
    public function onlyosn()
    {
        @date_default_timezone_set("Asia/Shanghai");
        $order_id_main = date('YmdHis') . rand(10000000, 99999999);
        //订单号码主体长度
        $order_id_len = strlen($order_id_main);
        $order_id_sum = 0;
        for ($i = 0; $i < $order_id_len; $i++) {
            $order_id_sum += (int)(substr($order_id_main, $i, 1));
        }
        //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
        $osn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT); //生成唯一订单号
        return $osn;
    }
}
