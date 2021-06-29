<?php


namespace App\Services\Pay\BR;


use App\Services\Pay\PayStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpePay extends PayStrategy
{

    protected static $url = 'https://pay.speedlyp.com/pay/recharge/order';    // 支付网关

    protected static $url_cashout = 'https://pay.speedlyp.com/api/withdrawal/order/add'; // 提现网关

    private $recharge_callback_url = '';     // 充值回调地址
    private $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;

    public $rechargeRtn = "SUCCESS";
    public $withdrawRtn = 'SUCCESS';

    public $company = 'spepay';   // 支付公司名

    public function _initialize()
    {
        $withdrawConfig = DB::table('settings')->where('setting_key', 'withdraw')->value('setting_value');
        $rechargeConfig = DB::table('settings')->where('setting_key', 'recharge')->value('setting_value');
        $withdrawConfig && $withdrawConfig = json_decode($withdrawConfig, true);
        $rechargeConfig && $rechargeConfig = json_decode($rechargeConfig, true);

        $this->withdrawMerchantID = isset($withdrawConfig[$this->company]) ? $withdrawConfig[$this->company]['merchant_id'] : "";
        $this->withdrawSecretkey = isset($withdrawConfig[$this->company]) ? $withdrawConfig[$this->company]['secret_key'] : "";

        $this->rechargeMerchantID = isset($rechargeConfig[$this->company]) ? $rechargeConfig[$this->company]['merchant_id'] : "";
        $this->rechargeSecretkey = isset($rechargeConfig[$this->company]) ? $rechargeConfig[$this->company]['secret_key'] : "";

        $this->recharge_callback_url = self::$url_callback . '/api/recharge_callback' . '?type=' . $this->company;
        $this->withdrawal_callback_url = self::$url_callback . '/api/withdrawal_callback' . '?type=' . $this->company;
    }

    public function generateSign($params, $flag = 1)
    {
        $secret = $flag == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if($value != "")
                $string[] = $key . '=' . $value;
        }
        $sign = implode('&', $string);
        $sign = urlencode($sign);
        Log::channel('mytest')->info('JunHe-login-sign-str',[$sign]);
        return hash_hmac('sha1',$sign,$secret);
    }

    public function rechargeSign($params)
    {
        $str = sprintf('payType=%d&merchantId=%s&amount=%s&orderId=%s&notifyUrl=%s&key=%s',$params['payType'],$params['merchantId'],$params['amount'],$params['orderId'],$params['notifyUrl'],$this->rechargeSecretkey);
        return md5($str);
    }

    public function rechargeCallbackSign($params)
    {
        $str = sprintf('merchantId=%s&amount=%s&orderId=%s&orderStatus=%d&key=%s',$params['merchantId'],$params['amount'],$params['orderId'],$params['orderStatus'],$this->rechargeSecretkey);
        return md5($str);
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'merchantId' => $this->rechargeMerchantID,
            'payType' => 101,
            'orderId' => $order_no,
            'amount' => intval($money),
            'redirectURL' => env('SHARE_URL',''),
            'notifyUrl' => $this->recharge_callback_url,
        ];
        $params['sign'] = $this->rechargeSign($params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('spepay_rechargeOrder', [$params]);
        $res = dopost(self::$url . 'v1/idpay/pay_center', http_build_query($params), []);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('spepay_rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "prepay failed";
            return false;
        }
        if($res['status'] != 0){
            $this->_msg = $res['message'];
            return false;
        }

        $native_url = $res['data']['payUrl'];
        $resData = [
            'pay_type' => $pay_type,
            'out_trade_no' => $order_no,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => '',
            'verify_money' => '',
            'match_code' => '',
            'params' => $params,
            'is_post' => isset($is_post) ? $is_post : 0
        ];
        return $resData;
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('spepay_rechargeCallback', $request->post());
        $params = $request->post();
        if($params['orderStatus'] != 1) {
            $this->_msg = 'SPE-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->rechargeCallbackSign($params) <> $sign) {
            $this->_msg = 'SPE-签名错误';
            return false;
        }
        $this->amount = $params['amount'];
        $where = [
            'order_no' => $params['orderId'],
        ];
        return $where;
    }

    /**
     *  后台审核请求提现订单 (提款)  代付方式
     */
    public function withdrawalOrder(object $withdrawalRecord)
    {
        $money = $withdrawalRecord->payment;    // 打款金额
        $order_no = $withdrawalRecord->order_no;

        $params = [
            'merchantId' => $this->withdrawMerchantID,
            'idCard' => $withdrawalRecord->bank_number,
            'orderId' => $order_no,
            'amount' => intval($money),
            'bankNumber' => 1,
            'bankName' => $withdrawalRecord->bank_name,
            'name' => $withdrawalRecord->account_holder,
            'accountNumber' => '',

            'appId' => $this->withdrawMerchantID,
            'terminalType' => 'app',
            'ts' => time() * 1000,
            'payType' => 4,
            'tradeAmount' => intval($money),
            'outOrderNo' => $order_no,
            'notifyUrl' => $this->withdrawal_callback_url,
            'upiAccount' => $withdrawalRecord->bank_number,
            'ifscCode' => '',
            'receiveName' => '',
            'receiveAccount' => '',
            'bankName' => '',
            'customerName' => $withdrawalRecord->account_holder,
        ];
        $params['sign'] = $this->generateSign($params, 2);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('JunHe_withdraw_params', $params);
        $res = $this->requestService->postFormData(self::$url_cashout, $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('JunHe_withdraw_return', [$res]);
        if (!$res) {
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['code'] != 200 && $res['success'] !== true) {
            $this->_msg = $res['message'];
            return false;
        }
        return [
            'pltf_order_no' => '',
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('JunHe_withdrawalCallback', $request->post());
        $params = $request->post();
//        if ($params['code'] != 1) {
//            $this->_msg = 'BRHX-withdrawal-交易未完成';
//            return false;
//        }
        $pay_status = 0;
        $status = (int)$params['orderState'];
        if ($status == 1) {
            $pay_status = 1;
        }
        if ($status == 2) {
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'JunHe-withdrawal-交易未完成';
            return false;
        }
        // 验证签名

        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSIgn($params, 2) <> $sign) {
            $this->_msg = 'JunHe-签名错误';
            return false;
        }
        $where = [
            'order_no' => $params['outOrderNo'],
            'plat_order_id' => '',
            'pay_status' => $pay_status
        ];
        return $where;
    }
}
