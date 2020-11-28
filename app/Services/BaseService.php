<?php


namespace App\Services;


use Illuminate\Support\Facades\Crypt;

abstract class BaseService
{
    public $_code = 200;

    public $_msg = "查询成功";

    public $_data = [];

    public function getUserId($token)
    {
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        return $data[0];
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
