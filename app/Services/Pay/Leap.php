<?php


namespace App\Services\Pay;

use App\Repositories\Api\UserRepository;
use App\Services\RequestService;
use Illuminate\Http\Request;

/**
 * 999666.in 的充值和提现类
 */
class Leap extends PayStrategy
{
    protected static $url = 'http://payqqqbank.payto89.com';    // 支付网关

    protected static $url_cashout = 'http://tqqqbank.payto89.com:82'; // 提现网关


//    public function __construct(RequestService $requestService, Request $request, UserRepository $userRepository)
//    {
//        parent::__construct($requestService, $request, $userRepository);
//    }

    /**
     * 生成签名  sign = Md5(key1=vaIue1&key2=vaIue2&key=签名密钥);
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


    public function testGetCallbackUrl()
    {
       return [
            'recharge_callback' => self::$url_callback . '/api/recharge_callback' . '?type=leap',
            'withdrawal_callback' => self::$url_callback . '/api/withdrawal_callback' . '?type=leap'
        ];
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $ip = $this->request->ip();
        $pay_type = 100;

        $notify_url = self::$url_callback . '/api/recharge_callback' . '?type=leap';

        $params = [
            'mch_id' => self::$merchantID,
            'ptype' => $pay_type,
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => 'recharge',
            'client_ip' => $ip,
            'format' => 'page',
            'notify_url' => $notify_url,
            'time' => time(),
        ];
        $params['sign'] = self::generateSign($params);
        $params = urlencode(json_encode($params));
        $res = $this->requestService->get(self::$url . '/order/getUrl?json=' . $params);
        if ($res['code'] <> 1) {
            $this->_msg = $res['msg'];
            return false;
        }
        $resData = [
            'out_trade_no' => $order_no,
            'shop_id' => self::$merchantID,
            'pay_type' => $pay_type,
            'native_url' => $res['data']['url'],
            'pltf_order_id' => '',
            'verify_money' => '',
            'match_code' => '',
            'notify_url' => $notify_url,
        ];
        return $resData;
    }
    /**
     *  后台审核请求提现订单 (提款)  代付方式
     */
    function withdrawalOrder(object $withdrawalRecord)
    {
        // 1 银行卡 2 Paytm 3代付
        $pay_type = 3;
        $onlyParams = $this->withdrawalOrderByDai($withdrawalRecord);
        $money = $withdrawalRecord->payment;    // 打款金额
        $ip = $this->request->ip();

//        $order_no = self::onlyosn();
        $order_no = $withdrawalRecord->order_no;

        $notify_url = self::$url_callback.'/api/withdrawal_callback'.'?type=leap';
        $params = [
            'type' => $pay_type,    // 1 银行卡 2 Paytm 3代付
            'mch_id' => self::$merchantID,
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => 'cashout',
            'client_ip' => $ip,
            'notify_url' => $notify_url,
            'time' => time(),
        ];
        $params = array_merge($params, $onlyParams);
        $params['sign'] = self::generateSign($params);

        $res = $this->requestService->postFormData(self::$url_cashout . '/order/cashout', $params);
        if ($res['code'] <> 1) {
            $this->_msg = $res['msg'];
            return false;
        }
        return  [
            'pltf_order_no' => '',
            'order_no' => $order_no,
            'notify_url' => $notify_url,
        ];
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        if ($request->state <> 4)  {
            $this->_msg = 'Leap-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if (self::generateSign($params) <> $sign) {
            $this->_msg = 'leap-签名错误';
            return false;
        }

        $where = [
            'order_no' => $request->sh_order,
        ];
        return $where;
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Leap_withdrawalCallback',$request->post());

        if ($request->state <> 4) {
            $this->_msg = 'Leap-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if (self::generateSign($params) <> $sign) {
            $this->_msg = 'leap-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->sh_order,
        ];
        return $where;
    }
}

