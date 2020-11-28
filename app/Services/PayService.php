<?php


namespace App\Services;


use Illuminate\Support\Facades\Crypt;

class PayService extends BaseService
{
    protected static $url = 'http://ipay-in.yynn.me';
    protected static $merchantID = 10175;
    protected static $secretkey = '1hmoz1dbwo2xbrl3rei78il7mljxdhqi';

    /**
     * 生成签名   sign = Md5(key1=vaIue1&key2=vaIue2…商户密钥);
     */
    protected static function generateSign(array $params)
    {
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
//        $sign = strtolower(implode('&', $string)) . self::$secretkey;
        $sign = (implode('&', $string)) . self::$secretkey;
        return md5($sign);
    }

    /**
     * 生成订单号
     */
    protected function onlyosn()
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
