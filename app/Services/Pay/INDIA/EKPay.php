<?php


namespace App\Services\Pay\INDIA;


use App\Services\Pay\PayStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EKPay extends PayStrategy
{

    protected static $url = 'https://gateway.ekpay.iwins.in/';    // 支付网关

    protected static $url_cashout = 'https://gateway.ekpays.com/'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'ekpay';   // 支付公司名 -- 印度

    public $rechargeRtn = "SUCCESS";
    public $withdrawRtn = 'SUCCESS';

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

    public function generateSign($params, $type=1)
    {
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if(!empty($value))
                $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&secret=' .  $secretKey;
        return strtoupper(md5($sign));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'merchant_no' => $this->rechargeMerchantID,
            'merchant_order' => $order_no,
            'amount' => intval($money),
            'return_url' => env('SHARE_URL',''),
            'notify_url' => $this->recharge_callback_url,
            'return_method' => 2,
        ];
        $params['sign'] = $this->generateSign($params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ekpay_rechargeOrder', [$params]);
        $res = dopost(self::$url . 'v1/idpay/pay_center', http_build_query($params), []);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ekpay_rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "prepay failed";
            return false;
        }
        if($res['code'] != 200){
            $this->_msg = $res['message'];
        }
        $native_url = $res['data']['url'];
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => '',
            'verify_money' => '',
            'match_code' => '',
            'is_post' => $is_post ?? 0,
        ];
        return $resData;
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ekpay_rechargeCallback',$request->input());
        $params = $request->input();
        if ($params['status'] != 2)  {
            $this->_msg = 'EK-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params) <> $sign) {
            $this->_msg = 'EK-签名错误';
            return false;
        }
        $this->amount = $params['paid'];
        $where = [
            'order_no' => $params['merchant_order'],
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
            'merchant_no' => $this->withdrawMerchantID,
            'merchant_order' => $order_no,
            'amount' => intval($money),
            'ifsc' => $withdrawalRecord->ifsc_code,
            'bank_name' => $withdrawalRecord->bank_name,
            'receiver_type' => 1,
            'receiver_account' => $withdrawalRecord->bank_number,
            'receiver_name' => $withdrawalRecord->account_holder,
            'notify_url' => $this->withdrawal_callback_url,
        ];

        $params['sign'] = $this->generateSign($params,2);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ekpay_withdrawal_params',$params);
        $res =dopost(self::$url_cashout . 'v1/idpay/remit', http_build_query($params), []);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ekpay_withdrawal_return', [$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['result_code'] != 200) {
            $this->_msg = $res['result_message'];
            return false;
        }
        return  [
            'pltf_order_no' => '',
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ek_withdrawalCallback',$request->input());
        $params = $request->input();
        $pay_status = 0;
        $status = $params['status'];
        if($status == 3){
            $pay_status= 1;
        }
        if($status == 4){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'ek-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'ek-签名错误';
            return false;
        }
        $where = [
            'order_no' =>$params['merchant_order'],
            'plat_order_id' => '',
            'pay_status' => $pay_status
        ];
        return $where;
    }

}

