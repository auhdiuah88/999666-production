<?php


namespace App\Services\Pay;

use App\Repositories\Api\UserRepository;
use App\Services\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
    Leap支付 充值和提现类
 */
class Leap extends PayStrategy
{
    protected static $url = 'http://payqqqbank.payto89.com';    // 支付网关

    protected static $url_cashout = 'http://tqqqbank.payto89.com:82'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    //public static $company = 'leap';   // 支付公司名

    public $merchantID;
    public $secretkey;
    public $company = 'leap';   // 支付公司名

    public function _initialize()
    {
//        self::$merchantID = config('pay.company.'.$this->company.'.merchant_id');
//        self::$secretkey = config('pay.company.'.$this->company.'.secret_key');
//        if (empty(self::$merchantID) || empty(self::$secretkey)) {
//            die('请设置 ipay 支付商户号和密钥');
//        }

        $config = DB::table('settings')->where('setting_key','withdraw')->value('setting_value');
//        $this->merchantID = config('pay.company.'.$this->company.'.merchant_id');
//        $this->secretkey = config('pay.company.'.$this->company.'.secret_key');
        $this->merchantID = isset($config[$this->company])?$config[$this->company]['merchant_id']:"";
        $this->secretkey = isset($config[$this->company])?$config[$this->company]['secret_key']:"";

        $this->recharge_callback_url = self::$url_callback . '/api/recharge_callback' . '?type='.$this->company;
        $this->withdrawal_callback_url =  self::$url_callback . '/api/withdrawal_callback' . '?type='.$this->company;
    }

    /**
     * 生成签名  sign = Md5(key1=vaIue1&key2=vaIue2&key=签名密钥);
     */
    public static function testGenerateSign(array $params)
    {
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' . self::$secretkey;
        dd(self::$secretkey);
        return md5($sign);
    }

    /**
     * 生成签名  sign = Md5(key1=vaIue1&key2=vaIue2&key=签名密钥);
     */
    public  function generateSign(array $params)
    {
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $this->secretkey;
        return md5($sign);
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $ip = $this->request->ip();
//        $pay_type = 100;
        $params = [
            'mch_id' => $this->merchantID,
            'ptype' => 100,
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => 'recharge',
            'client_ip' => $ip,
            'format' => 'page',
            'notify_url' => $this->recharge_callback_url,
            'time' => time(),
        ];
        $params['sign'] = $this->generateSign($params);
        $params = urlencode(json_encode($params));

        \Illuminate\Support\Facades\Log::channel('mytest')->info('leap_rechargeOrder', [$params]);

        $res = $this->requestService->get(self::$url . '/order/getUrl?json=' . $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('leap_rechargeOrder', [$res]);
        if ($res['code'] <> 1) {
            $this->_msg = $res['msg'];
            return false;
        }
        $resData = [
            'out_trade_no' => $order_no,
            'shop_id' => $this->merchantID,
            'pay_company' => $this->company,
            'pay_type' => $pay_type,
            'native_url' => $res['data']['url'],
            'pltf_order_id' => '',
            'verify_money' => '',
            'match_code' => '',
            'notify_url' => $this->recharge_callback_url,
        ];
        return $resData;
    }

    /**
     *  通过代付提款
     */
    private function withdrawalOrderByDai(object $withdrawalRecord)
    {
//        $bank_name = $withdrawalRecord->bank_name;
        $account_holder = $withdrawalRecord->account_holder;
        $bank_number = $withdrawalRecord->bank_number;
        $ifsc_code = $withdrawalRecord->ifsc_code;
        $phone = $withdrawalRecord->phone;
        $email = $withdrawalRecord->email;

        // 各个支付独有的参数
        $onlyParams = [
            'bank_name' => $account_holder, // 收款姓名（类型为1,3不可空，长度0-200)
            'bank_card' => $bank_number,    // 收款卡号（类型为1,3不可空，长度9-26
            'ifsc' => $ifsc_code,           // ifsc代码 （类型为1,3不可空，长度9-26）
            'bank_tel' => $phone,           // 收款手机号（类型为3不可空，长度0-20）
            'bank_email' => $email,         // 收款邮箱（类型为3不可空，长度0-100）
        ];
        return $onlyParams;
    }

    /**
     *  后台审核请求提现订单 (提款)  代付方式
     */
    public function withdrawalOrder(object $withdrawalRecord)
    {

        // 1 银行卡 2 Paytm 3代付
        $pay_type = 3;
        $onlyParams = $this->withdrawalOrderByDai($withdrawalRecord);
        $money = $withdrawalRecord->payment;    // 打款金额
        $ip = $this->request->ip();
//        $order_no = self::onlyosn();
        $order_no = $withdrawalRecord->order_no;
        $params = [
            'type' => $pay_type,    // 1 银行卡 2 Paytm 3代付
            'mch_id' => $this->merchantID,
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => 'withdrawal',
            'client_ip' => $ip,
            'notify_url' => $this->withdrawal_callback_url,
            'time' => time(),
        ];
        $params = array_merge($params, $onlyParams);
        $params['sign'] = $this->generateSign($params);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('leap_withdrawalOrder',$params);

        $res = $this->requestService->postFormData(self::$url_cashout . '/order/cashout', $params);
        if ($res['code'] <> 1) {
            $this->_msg = $res['msg'];
            return false;
        }
        return  [
            'pltf_order_no' => '',
            'order_no' => $order_no,
            'notify_url' => $this->withdrawal_callback_url,
        ];
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Leap_rechargeCallback',$request->post());

        if ($request->state <> 4)  {
            $this->_msg = 'Leap-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if ($this->generateSign($params) <> $sign) {
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
            $this->_msg = 'Leap-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if ($this->generateSign($params) <> $sign) {
            $this->_msg = 'leap-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->sh_order,
        ];
        return $where;
    }
}

