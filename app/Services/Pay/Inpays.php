<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Inpays extends PayStrategy
{

    protected static $url = 'https://www.inpays.in/';    // 网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'inpays';   // 支付公司名

    public function _initialize()
    {
        $withdrawConfig = DB::table('settings')->where('setting_key','withdraw')->value('setting_value');
        $rechargeConfig = DB::table('settings')->where('setting_key','recharge')->value('setting_value');
        $withdrawConfig && $withdrawConfig = json_decode($withdrawConfig,true);
        $rechargeConfig && $rechargeConfig = json_decode($rechargeConfig,true);
//        $this->merchantID = config('pay.company.'.$this->company.'.merchant_id');
//        $this->secretkey = config('pay.company.'.$this->company.'.secret_key');
        $this->withdrawMerchantID = isset($withdrawConfig[$this->company])?$withdrawConfig[$this->company]['merchant_id']:"";
        $this->withdrawSecretkey = isset($withdrawConfig[$this->company])?$withdrawConfig[$this->company]['secret_key']:"";

        $this->rechargeMerchantID = isset($rechargeConfig[$this->company])?$rechargeConfig[$this->company]['merchant_id']:"";
        $this->rechargeSecretkey = isset($rechargeConfig[$this->company])?$rechargeConfig[$this->company]['secret_key']:"";

        $this->recharge_callback_url = self::$url_callback . '/api/recharge_callback' . '?type='.$this->company;
        $this->withdrawal_callback_url =  self::$url_callback . '/api/withdrawal_callback' . '?type='.$this->company;
    }

    /**
     * 生成签名  sign = Md5(key1=vaIue1&key2=vaIue2&key=签名密钥);
     */
    public  function generateSign(array $params, $type=1)
    {
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $secretKey;
        return strtolower(md5($sign));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'merchant' => $this->rechargeMerchantID,
            'orderId' => $order_no,
            'amount' => intval($money),
            'customName' => 'Customer',
            'customMobile' => '88888888',
            'customEmail' => '88888888@in.com',
            'channelType' => 'UPI',
            'notifyUrl' => $this->recharge_callback_url,
            'callbackUrl' => env('SHARE_URL',''),
        ];
        $params['sign'] = $this->generateSign($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('inpays_rechargeOrder', [$params]);
        $res = $this->requestService->postFormData(self::$url . 'openApi/pay/createOrder' , $params,[
            "content-type" => "application/x-www-form-urlencoded",
        ]);
        if ($res['code'] != 200) {
            \Illuminate\Support\Facades\Log::channel('mytest')->info('inpays_rechargeOrder_return', $res);
            $this->_msg = $res['errorMessages'];
            return false;
        }
        $native_url = $res['data']['url'];
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['data']['platOrderId'],
            'verify_money' => '',
            'match_code' => '',
            'is_post' => isset($is_post)?$is_post:0,
            'params' => []
        ];
        return $resData;
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('inpays_rechargeCallback',$request->post());

        if ($request->status != 'PAY_SUCCESS')  {
            $this->_msg = 'inpays-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,1) <> $sign) {
            $this->_msg = 'inpays-签名错误';
            return false;
        }

        $where = [
            'order_no' => $request->orderId,
        ];
        return $where;
    }

    /**
     *  后台审核请求提现订单 (提款)  代付方式
     */
    public function withdrawalOrder(object $withdrawalRecord)
    {
        $money = $withdrawalRecord->payment;    // 打款金额
//        $ip = $this->request->ip();
//        $order_no = self::onlyosn();
        $order_no = $withdrawalRecord->order_no;
        $params = [
            'appId' => $this->withdrawMerchantID,
            'outOrderNo' => $order_no,
            'applyDate' => date('Y-m-d H:i:s'),
            'channel' => '912',
            'notifyUrl' => $this->withdrawal_callback_url,
            'amount' => intval($money),
            'mode' => 'UPI',
            'account' => $withdrawalRecord->bank_number,
            'userId' => $withdrawalRecord->user_id,
            'clientIp' => $this->request->ip(),
        ];
        $params['sign'] = $this->generateSign($params,2);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('rspay_withdrawalOrder',$params);
        $res = $this->requestService->postJsonData(self::$url . 'payout', $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('rspay_withdrawalOrder2',$res);
        if ($res['statusCode'] != '00') {
            $this->_msg = $res['message'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['transactionId'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('rspay_withdrawalCallback',$request->post());

        if ((string)($request->payStatus) != '11') {
            $this->_msg = 'rspay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if ($this->generateSignRigorous($params,2) <> $sign) {
            $this->_msg = 'MTBpay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->outOrderNo,
            'plat_order_id' => $request->transactionId
        ];
        return $where;
    }

    protected function makeRequestNo($withdraw_id){
        return date('YmdDis') . $withdraw_id;
    }

    /**
     * 请求待付状态
     * @param $withdrawalRecord
     * @return array|false|mixed|string
     */
    public function callWithdrawBack($withdrawalRecord){
        $request_no = $this->makeRequestNo($withdrawalRecord->id);
        $request_time = date("YmdHis");
        $mer_no = $this->merchantID;
        $mer_order_no = $withdrawalRecord->order_no;

        $params = compact('request_no','request_time','mer_no','mer_order_no');
        $params['sign'] = $this->generateSign($params,2);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('MTBpay_withdrawSingleQuery_Param',$params);
        $res = $this->requestService->postJsonData(self::$url_cashout . 'withdraw/singleQuery', $params);
        if(!$res){
            return false;
        }
        if($res['query_status'] != 'SUCCESS'){
            \Illuminate\Support\Facades\Log::channel('mytest')->info('MTBpay_withdrawSingleQuery_Err',$res);
            return false;
        }
        return $res;
    }

}
