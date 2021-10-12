<?php


namespace App\Services\Pay\INDIA;


use App\Services\Pay\PayStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarsPay extends PayStrategy
{

    protected static $url_pay = 'https://api.stars555555.com/okex-admin/okex/api/v2/pay';    // 支付网关

    protected static $url_withdraw = 'https://api.stars555555.com/okex-admin/okex/api/v2/df'; // 提现网关

    /**
     * spero支付--印度
     * @var int
     */
    public $company = 'marspay';   // 支付公司名 -- 印度

    public $rechargeRtn = 'success';
    public $withdrawRtn = 'success';

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;

    public function _initialize()
    {
        parent::_initialize();
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
            if($value != "")
                $string[] = $key . '=' . $value;
        }
        $sign = implode("&",$string ) . "&key=" . $secretKey;
        \Illuminate\Support\Facades\Log::channel('mytest')->info('marspay_sign', [$sign]);
        $sign = strtoupper(md5($sign));

        return $sign;
    }

    function rechargeOrder($way, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'merchantNo' => $this->rechargeMerchantID,
            'orderNo' => $order_no,
            'amount' => sprintf('%.2f',$money),
            'type' => 8,
            'returnUrl' => self::$url_callback,
            'notifyUrl' => $this->recharge_callback_url,
            'userName' => 'recharge balance',
            'ext' => $order_no,
            'version' => '2.0.3',
        ];
        $params['sign'] = $this->generateSign($params);
        $params = json_encode($params);
        $header[] = "Content-Type: application/json; charset=utf-8";
        $header[] = 'Content-Length: ' . strlen($params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('marspay_params', [$params]);
        $res =dopost(self::$url_pay, $params, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('marspay_return', [$params]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "prepay failed";
            return false;
        }
        if($res['code'] !=  0){
            $this->_msg = $res['message'];
            return false;
        }
        $native_url = $res['url'];

        $resData = [
            'out_trade_no' => $order_no,
            'channel' => $this->channelId,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['platformOrderNo'],
            'verify_money' => '',
            'match_code' => '',
            'is_post' => isset($is_post)?$is_post:0
        ];
        return $resData;
    }

    function withdrawalOrder($withdrawalRecord)
    {
        $money = $withdrawalRecord->payment;    // 打款金额
        $order_no = $withdrawalRecord->order_no;
        $params = [
            'merchantNo' => $this->withdrawMerchantID,
            'orderNo' => $order_no,
            'amount' => sprintf('%.2f',$money),
            'type' => 1,
            'ext' => $order_no,
            'version' => '2.0.3',
            'name' => 'aaa',
            'account' => $withdrawalRecord->bank_number,
            'ifscCode' => $withdrawalRecord->ifsc_code,
            'notifyUrl' => $this->withdrawal_callback_url,
        ];
        $params['sign'] = $this->generateSign($params,2);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json; charset=utf-8";
        $header[] = 'Content-Length: ' . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('marspay_withdraw_params',$params);
        $res =dopost(self::$url_withdraw, $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('marspay_withdraw_return',$params);
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

    function rechargeCallback(Request $request)
    {
        try{
            $params = $request->input();
            \Illuminate\Support\Facades\Log::channel('mytest')->info('recharge_callback_marspay',$request->input());
            if ($params['status'] != 1)  {
                $this->_msg = 'marspay-recharge-交易未完成';
                return false;
            }
            // 验证签名
            $sign = $params['sign'];
            unset($params['channelId']);
            unset($params['sign']);
            if ($this->generateSign($params) <> $sign) {
                $this->_msg = 'marspay-签名错误';
                return false;
            }
            $this->amount = $params['amount'];
            $where = [
                'order_no' => $params['orderNo'],
            ];
            return $where;
        }catch(\Exception $e){
            Log::mylog('marspay_recharge_error',[$e->getMessage()],'recharge');
            return [];
        }
    }

    function withdrawalCallback(Request $request)
    {
        $params = $request->input();
        \Illuminate\Support\Facades\Log::channel('mytest')->info('withdraw_callback_marspay',$request->input());
        $pay_status = 0;
        $status = $params['status'];
        if($status == 1){
            $pay_status= 1;
        }
        if($status == 3){
            $pay_status = 2;
        }
        if ($pay_status == 0) {
            $this->_msg = 'marspay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名

        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['channelId']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'marspay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $params['orderNo'],
            'plat_order_id' => $params['platformOrderNo'],
            'pay_status' => $pay_status
        ];
        return $where;
    }
}
