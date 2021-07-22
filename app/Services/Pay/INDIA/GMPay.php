<?php


namespace App\Services\Pay\INDIA;


use App\Services\Pay\PayStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GMPay extends PayStrategy
{

    protected static $url = 'https://gmpay.in/api/pay/createPay';    // 支付网关

    protected static $url_cashout = 'https://gmpay.in/api/without/loanPay'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'gmpay';   // 支付公司名 -- 印度

    public $rechargeRtn = "success";
    public $withdrawRtn = 'success';

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
            if($value != '')
                $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $secretKey;
        return strtoupper(md5($sign));
    }

    public function generateRechargeSign($params)
    {
        $sign_str = sprintf('%s,%s,%d,%s', $params['merchantId'], $params['merOrderId'], $params['merUserId'], $params['amount']);
        $sign_str = strtolower($sign_str) . $this->rechargeSecretkey;
        return md5($sign_str);
    }

    public function generateWithdrawSign($params)
    {
        $sign_str = sprintf('%s,%s,%s', $params['merchantId'], $params['merOrderId'], $params['amount']);
        $sign_str = strtolower($sign_str) . $this->withdrawSecretkey;
        return md5($sign_str);
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $user_id = $this->getUserId();
        $params = [
            'merchantId' => $this->rechargeMerchantID,
            'merOrderId' => $order_no,
            'merUserId' => $user_id,
            'amount' => (string)intval($money),
            'frontUrl' => env('SHARE_URL',''),
            'backUrl' => $this->recharge_callback_url,
        ];
        $params['sign'] = $this->generateRechargeSign($params);
        $header[] = "Content-Type: application/x-www-form-urlencoded";
        \Illuminate\Support\Facades\Log::channel('mytest')->info('gmpay_rechargeOrder', [$params]);
        $res = dopost(self::$url, http_build_query($params), $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('gmpay_rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "prepay failed";
            return false;
        }
        if($res['code'] != '0000'){
            $this->_msg = $res['msg'];
            return false;
        }
        $native_url = $res['data']['payurl'];
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['data']['platformid'],
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('gmpay_rechargeCallback',$request->input());
        $params = $request->input();
        if ($params['respCode'] != '0000')  {
            $this->_msg = 'gmpay-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['signature'];
        unset($params['signature']);
        unset($params['type']);
        if ($this->generateRechargeSign($params) <> $sign) {
            $this->_msg = 'gmpay-签名错误';
            return false;
        }
        $this->amount = $params['amount'];
        $where = [
            'order_no' => $params['merchantId'],
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
            'merOrderId' => $order_no,
            'amount' => (string)intval($money),
            'name' => $withdrawalRecord->account_holder,
            'ifsc' => $withdrawalRecord->ifsc_code,
            'accountNumber' => $withdrawalRecord->bank_number,
            'bank' => $withdrawalRecord->bank_name,
            'backurl' => $this->withdrawal_callback_url,
        ];
        $params['sign'] = $this->generateWithdrawSign($params);
        $header[] = "Content-Type: application/x-www-form-urlencoded";
        \Illuminate\Support\Facades\Log::channel('mytest')->info('gmpay_withdrawal_params',$params);
        $res =dopost(self::$url_cashout, http_build_query($params), $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('gmpay_withdrawal_return', [$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['code'] != '0000') {
            $this->_msg = $res['msg'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['data']['platformid'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('gmpay_withdrawalCallback',$request->input());
        $params = $request->input();
        $pay_status = 0;
        $status = $params['respCode'];
        if($status == '0000'){
            $pay_status= 1;
        }
        if($status == '1111'){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'gmpay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['signature'];
        unset($params['signature']);
        unset($params['type']);
        if ($this->generateWithdrawSign($params) <> $sign) {
            $this->_msg = 'gmpay-签名错误';
            return false;
        }
        $where = [
            'order_no' =>$params['merOrderId'],
            'plat_order_id' => '',
            'pay_status' => $pay_status
        ];
        return $where;
    }

}

