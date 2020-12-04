<?php


namespace App\Services\Pay;

use Illuminate\Http\Request;

/**
 *  如：unicasino.in  的充值和提现类
 */
class Ipay extends PayStrategy
{

    protected static $url = 'http://ipay-in.yynn.me';

    protected static $url_callback = 'http://api.unicasino.in';    // 回调地址 (充值或提现)

    // 正式环境
    protected static $merchantID = 10175;
    protected static $secretkey = '1hmoz1dbwo2xbrl3rei78il7mljxdhqi';


    // 测试环境
//    protected static $merchantID = 10120;
//    protected static $secretkey = 'j3phc11lg986dx3tkai120ngpxy7a2sw';

    /**
     * 生成签名   sign = Md5(key1=vaIue1&key2=vaIue2…商户密钥);
     */
    public static function generateSign(array $params)
    {
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . self::$secretkey;
        return md5($sign);
    }

    /**
     * 充值下单接口
     */
    function rechargeOrder($pay_type,$money)
    {
        $order_no = self::onlyosn();
        $params = [
            'api_name' => 'quickpay.all.native',
            'money' => $money,
            'notify_url' => self::$url_callback.'/api/recharge_callback'.'?type=ipay',
            'order_des' => '支付充值',
            'out_trade_no' => $order_no,
            'shop_id' => self::$merchantID,
        ];
        $params['sign'] = self::generateSign($params);

        $res = $this->requestService->postJsonData(self::$url . '/pay', $params);
        if ($res['rtn_code'] <> 1000) {
            $this->_msg = $res['rtn_msg'];
//            $this->_data = $res;
            return false;
        }
        $resData = [
            'out_trade_no' => $order_no,
            'shop_id' => self::$merchantID,
            'pay_type' => $pay_type,
            'native_url' => $res['native_url'],
            'pltf_order_id' => $res['pltf_order_id'],
            'verify_money' => $res['verify_money'],
            'match_code' => $res['match_code'],
        ];
        return $resData;
    }

    function withdrawalOrder(Request $request)
    {
        // TODO: Implement withdrawalOrder() method.
    }

    function rechargeCallback(Request $request)
    {
        // 验证参数
        if ($request->shop_id <> self::$merchantID
            || $request->api_name <> 'quickpay.all.native.callback'
            || $request->pay_result <> 'success'
        ) {
            $this->_msg = '参数错误';
            return false;
        }

        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if (self::generateSign($params) <> $sign){
            $this->_msg = '签名错误';
            return false;
        }

        $where = [
            'order_no' => $request->out_trade_no,
            'pltf_order_id' => $request->pltf_order_id,
//            'money' => $money
        ];

        return $where;
    }
}

