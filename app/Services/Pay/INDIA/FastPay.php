<?php


namespace App\Services\Pay\INDIA;


use App\Services\Pay\PayStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FastPay extends PayStrategy
{

    protected static $url = 'https://api.fast8866.com/okex-admin/okex/api/v2/pay';    // 支付网关

    protected static $url_cashout = 'https://api.fast8866.com/okex-admin/okex/api/v2/df'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'fastpay';   // 支付公司名 -- 印度

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

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'merchantNo' => $this->rechargeMerchantID,
            'orderNo' => $order_no,
            'amount' => (string)intval($money),
            'type' => 8,
            'notifyUrl' => $this->recharge_callback_url,
            'userName' => 'tom',
            'ext' => 'recharge',
            'version' => '2.0.3',
        ];
        $params['sign'] = $this->generateSign($params);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('fastpay_rechargeOrder', [$params]);
        $res = dopost(self::$url, $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('fastpay_rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "prepay failed";
            return false;
        }
        if($res['code'] != 0){
            $this->_msg = $res['message'];
            return false;
        }
        $native_url = $res['url'];
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['platformOrderNo'],
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('fastpay_rechargeCallback',$request->input());
        $params = $request->input();
        if ($params['status'] != 1)  {
            $this->_msg = 'fastpay-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params) <> $sign) {
            $this->_msg = 'fastpay-签名错误';
            return false;
        }
        $this->amount = $params['amount'];
        $where = [
            'order_no' => $params['orderNo'],
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
            'merchantNo' => $this->withdrawMerchantID,
            'orderNo' => $order_no,
            'amount' => (string)intval($money),
            'type' => 1,
            'notifyUrl' => $this->withdrawal_callback_url,
            'ext' => 'withdraw',
            'version' => '2.0.3',
            'name' => $withdrawalRecord->account_holder,
            'account' => $withdrawalRecord->bank_number,
            'ifscCode' => $withdrawalRecord->ifsc_code,
        ];
        $params['sign'] = $this->generateSign($params,2);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('fastpay_withdrawal_params',$params);
        $res =dopost(self::$url_cashout, $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('fastpay_withdrawal_return', [$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['code'] != 0) {
            $this->_msg = $res['message'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['platformOrderNo'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('fastpay_withdrawalCallback',$request->input());
        $params = $request->input();
        $pay_status = 0;
        $status = $params['status'];
        if($status == 1){
            $pay_status= 1;
        }
        if($status == 3){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'fastpay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'fastpay-签名错误';
            return false;
        }
        $where = [
            'order_no' =>$params['orderNo'],
            'plat_order_id' => $params['platformOrderNo'],
            'pay_status' => $pay_status
        ];
        return $where;
    }

}

