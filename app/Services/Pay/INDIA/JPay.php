<?php


namespace App\Services\Pay\INDIA;


use App\Services\Pay\PayStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JPay extends PayStrategy
{

    protected static $url = 'https://api.ydjukw.com/pay/center/deposit/apply';    // 支付网关

    protected static $url_cashout = 'https://api.ydjukw.com/pay/center/withdrawal/apply'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'jpay';   // 支付公司名 -- 印度

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
//        var_dump($params);exit();
        $string = "";
        foreach ($params as $key => $val) {
            if($val)
                $string .= strval($val);
        }
        $sign = $string . $secretKey;
//        var_dump($sign);exit();
//        Log::mylog("ifpay-signstr",[$sign],'recharge');
        return md5($sign);
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'merchantCode' => $this->rechargeMerchantID,
            'merchantTradeNo' => $order_no,
            'userId' => 'jpay',
            'amount' => sprintf("%.2f",$money),
            'notifyUrl' => $this->recharge_callback_url,
            'returnUrl' => env('APP_URL',''),
            'terminalType' => 1,
            'channel' => 'AlipayBank',
        ];
        $params['sign'] = $this->generateSign($params);

        $params = [
            'merchantCode' => $this->rechargeMerchantID,
            'signType' => 'md5',
            'content' => json_encode($params)
        ];
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "charset: UTF-8";
        \Illuminate\Support\Facades\Log::channel('mytest')->info('jpay_rechargeOrder', [$params]);
        $res = dopost(self::$url, $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('jtpay_rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "prepay failed";
            return false;
        }
        if ($res['status'] != 'SUCCESS') {
            $this->_msg = $res['msg'];
            return false;
        }
        $content = json_decode($res['data']);
        $content = json_decode($content->content);
        $native_url = $content->payUrl;;
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $content->tradeNo,
            'verify_money' => intval($money),
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('jpay_rechargeCallback',$request->input());
        $params = $request->input();
        if ($params['trade_status'] !== 'PAYMENT_SUCCESS')  {
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
            'merchantCode' => $this->withdrawMerchantID,
            'merchantTradeNo' => $order_no,
            'accountName' => $withdrawalRecord->account_holder,
            'bankCode' => $withdrawalRecord->ifsc_code,
            'bankCardNumber' => $withdrawalRecord->bank_number,
            'amount' => sprintf("%.2f",$money),
            'notifyUrl' => $this->withdrawal_callback_url,
            'channel' => 'Withdraw',
        ];
        $params['sign'] = $this->generateSign($params,2);
        $params = [
            'merchantCode' => $this->withdrawMerchantID,
            'signType' => 'md5',
            'content' => json_encode($params)
        ];
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "charset: UTF-8";
        \Illuminate\Support\Facades\Log::channel('mytest')->info('jpay_withdrawal_params',$params);
        $res =dopost(self::$url_cashout, $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('jpay_withdrawal_return', [$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['status'] !== 'SUCCESS') {
            $this->_msg = $res['msg'];
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('jpay_withdrawalCallback',$request->input());
        $params = $request->input();
        $pay_status = 0;
        $status = $params['status'];
        if($status === 'WITHDRAWAL_SUCCESS'){
            $pay_status= 1;
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

