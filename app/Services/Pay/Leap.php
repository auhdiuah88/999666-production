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

    protected static $url_callback = 'http://api.999666.in';    // 回调地址 (充值或提现)

    // 正式环境
    protected static $merchantID = 262593573;
    protected static $secretkey = '4e70f59ec59149a6b81d26aafed8f6fb';

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

    public function test($ip)
    {
        $order_no = self::onlyosn();
        $money = 20000;
        $params = [
            'mch_id' => self::$merchantID,
            'ptype' => 100,                   // Paytm支付：1     银行卡：3
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => '充值',
            'client_ip' => $ip,
            'format' => 'https://www.baidu.com',
            'notify_url' => self::$url_callback . '/api/recharge_callback',
            'time' => time(),
        ];
        $params['sign'] = self::generateSign($params);

        $res = $this->requestService->postFormData(self::$url . '/order/place', $params, [], 'body');
        // 写入文件
        $path = public_path('a.html');
        file_put_contents($path, $res);

        return $res;
    }

    public function test2($ip)
    {
        $order_no = self::onlyosn();
        $money = 200;
        $params = [
            'mch_id' => self::$merchantID,
            'ptype' => 100,
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => '充值',
            'client_ip' => $ip,
            'format' => 'page',
            'notify_url' => self::$url_callback . '/api/recharge_callback',
            'time' => time(),
        ];
        $params['sign'] = self::generateSign($params);
        $params = urlencode(json_encode($params));
        $res = $this->requestService->get(self::$url . '/order/getUrl?json=' . $params);
        return $res;
    }

    /**
     * 充值下单接口-跳转选择支付类型页面
     * post 方式
     */
    public function rechargeTypeSelect(Request $request)
    {
        $money = $request->money;
        $order_no = self::onlyosn();
        $params = [
            'mch_id' => self::$merchantID,
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => '充值',
            'client_ip' => $request->ip(),
            'format' => 'https://www.baidu.com',
            'notify_url' => self::$url_callback . '/api/recharge_callback',
            'time' => time(),
        ];
        $params['sign'] = self::generateSign($params);
        $res = $this->requestService->postFormData(self::$url . '/order/placeForIndex', $params);
        dd($res);
    }

    /**
     * 充值下单接口-通用
     * POST方式  返回html源码
     */
    public function rechargeOrderHtml(Request $request)
    {
        $user_id = $this->getUserId($request->header("token"));
        $user = $this->userRepository->findByIdUser($user_id);
        $pay_type = $request->pay_type;
        $money = $request->money;
        $order_no = self::onlyosn();
        $pay_type = 1;
        $params = [
            'mch_id' => self::$merchantID,
            'ptype' => $pay_type,       // Paytm支付：1     银行卡：3
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => '充值',
            'client_ip' => $request->ip(),
            'format' => 'https://www.baidu.com',
            'notify_url' => self::$url_callback . '/api/recharge_callback',
            'time' => time(),
        ];
        $params['sign'] = self::generateSign($params);
        $res = $this->requestService->postJsonData(self::$url . '/order/place', $params, [], 'body');
        $this->rechargeRepository->addRechargeLog($user, $money, $order_no, $pay_type);
        return $res;
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $ip = $this->request->ip();
        $pay_type = 100;
        $params = [
            'mch_id' => self::$merchantID,
            'ptype' => $pay_type,
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => '充值',
            'client_ip' => $ip,
            'format' => 'page',
            'notify_url' => self::$url_callback . '/api/recharge_callback'.'?type=leap',
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
        ];
        return $resData;
    }

    /**
     *  通过代付提款
     */
    function withdrawalOrderByDai($user_id,$bank_id)
    {
        $user_bank = $this->userRepository->getBankByBankId($bank_id); 
        if ($user_bank->user_id <> $user_id) {
            $this->_msg = '银行卡不匹配';
            return false;
        }

        $account_holder = $user_bank->account_holder;
        $bank_name = $user_bank->bank_type_id;
        $bank_number = $user_bank->bank_num;
        $ifsc_code = $user_bank->ifsc_code;
        $phone = $user_bank->phone;
        $mail = $user_bank->mail;
        
        $onlyParams = [];  // 各个支付独有的参数
        $onlyParams = [
            'bank_name' => $account_holder, // 收款姓名（类型为1,3不可空，长度0-200)
            'bank_card' => $bank_number,   // 收款卡号（类型为1,3不可空，长度9-26
            'ifsc' => $ifsc_code,   // ifsc代码 （类型为1,3不可空，长度9-26）
            'bank_tel' => $phone,   // 收款手机号（类型为3不可空，长度0-20）
            'bank_email' => $mail,   // 收款邮箱（类型为3不可空，长度0-100）
        ];
        return $onlyParams;
    }

    /**
     *  后台请求提现订单 (提款)  代付方式
     */
    function withdrawalOrder(Request $request)
    {
        $user_id = $request->user_id;
        $bank_id = $request->bank_id;
        $money = $request->money;
        // 1 银行卡 2 Paytm 3代付
        $pay_type = 3;
        $onlyParams = $this->withdrawalOrderByDai($user_id, $bank_id);

        $order_no = self::onlyosn();
        $ip = $this->request->ip();
        $params = [
            'type' => $pay_type,    // 1 银行卡 2 Paytm 3代付
            'mch_id' => self::$merchantID,
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => '提现',
            'client_ip' => $ip,
            'notify_url' => self::$url_callback.'/api/withdrawal_callback'.'?=type=leap',
            'time' => time(),
        ];
        $params = array_merge($params, $onlyParams);
        $params['sign'] = self::generateSign($params);

        $res = $this->requestService->postFormData(self::$url_cashout . '/order/cashout', $params);
        if ($res['code'] <> 1) {
            $this->_msg = $res['msg'];
            return false;
        }
        return $res;
    }

    function rechargeCallback(Request $request)
    {
        if ($request->state <> 4)  {
            $this->_msg = '交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if (self::generateSign($params) <> $sign) {
            $this->_msg = '签名错误';
            return false;
        }

        $where = [
            'order_no' => $request->sh_order,
//            'pltf_order_id' => $request->pltf_order_id,
//            'money' => $money
        ];
        return $where;
    }
}

