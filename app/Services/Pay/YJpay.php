<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class YJpay extends PayStrategy
{

    protected static $url = 'https://usdt1788.in/center/api/prepay.do';    // 支付网关

    protected static $url_cashout = 'https://usdt1788.in/center/api/payout.do'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'YJpay';   // 支付公司名

    public $rechargeRtn = "SUCCESS";
    public $withdrawRtn = "SUCCESS";
    public $amountFiled = "amount";

    public function _initialize()
    {
        $withdrawConfig = DB::table('settings')->where('setting_key','withdraw')->value('setting_value');
        $rechargeConfig = DB::table('settings')->where('setting_key','recharge')->value('setting_value');
        $withdrawConfig && $withdrawConfig = json_decode($withdrawConfig,true);
        $rechargeConfig && $rechargeConfig = json_decode($rechargeConfig,true);
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
    public function generateSignRigorous(array $params, $type=1){
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if(!empty($value))
                $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $secretKey;
        return strtoupper(md5($sign));
    }

    /**
     * 生成签名  sign = Md5(key1=vaIue1&key2=vaIue2&key=签名密钥);
     */
    public function generateSignRigorous2(array $params, $type=1){
        $secretKey = "bb8df40c64b34d1481b3a65b0742b1a1";
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if(!empty($value))
                $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $secretKey;
        \Illuminate\Support\Facades\Log::channel('mytest')->info('YJ_rechargeCallback',[$sign]);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('YJ_rechargeCallback',[strtoupper(md5($sign))]);
        return strtoupper(md5($sign));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'merchantId' => $this->rechargeMerchantID,
            'tradeNo' => $order_no,
            'paymentType' => 1,
            'amount' => intval($money * 100),
            'currency' => "INR",
            'callback' => env('SHARE_URL',''),
            'notify' =>  $this->recharge_callback_url,
            'version' => "v1.0"
        ];
        $params['sign'] = $this->generateSignRigorous($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('YJ_rechargeOrder', [$params]);

        $res = $this->requestService->postFormData(self::$url, $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('YJ_rechargeOrder_return', $res);
        if ($res['code'] != 0) {
            \Illuminate\Support\Facades\Log::channel('mytest')->info('YJ_rechargeOrder_return', $res);
            $this->_msg = $res['msg'];
            return false;
        }
        $native_url = $res['data']['url'];
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['data']['orderNo'],
            'verify_money' => $res['data']['amount'],
            'match_code' => '',
            'is_post' => isset($is_post)?$is_post:0
        ];
        return $resData;
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        try{
            \Illuminate\Support\Facades\Log::channel('mytest')->info('YJ_rechargeCallback',$request->input());
            $data = $request->input();
            if ((int)$data['code'] != 0)  {
                $this->_msg = 'YJ-recharge-交易未完成';
                return false;
            }
            // 验证签名
            $params = $data['data'];
            if(!is_array($params))$params = json_decode($params,true);
            $sign = $params['sign'];
            unset($params['sign']);
            unset($params['type']);
//            $params['amount'] = intval($params['amount']);
//            $params['fee'] = intval($params['fee']);
            if ($this->generateSignRigorous($params,1) <> $sign) {
                $this->_msg = 'YJ-签名错误';
                return false;
            }
            $where = [
                'order_no' => $params['tradeNo'],
            ];
            return $where;
        }catch(\Exception $e){
            \Illuminate\Support\Facades\Log::channel('mytest')->info('err_YJ_rechargeCallback',[$request->input(), $e->getMessage(), $e->getLine()]);
            return [];
        }
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
            'tradeNo' => $order_no,
            'type' => 1,
            'name' => $withdrawalRecord->account_holder,
            'account' => $withdrawalRecord->bank_number,
            'bankCode' => "IDPT0001",
            'branchCode' => $withdrawalRecord->ifsc_code,
            'email' => $withdrawalRecord->mail,
            'mobile' => $withdrawalRecord->phone,
            'amount' => intval($money * 100),
            'currency' => "INR",
            'version' => "v1.0",
            'notify' => $this->withdrawal_callback_url,
        ];
        $params['sign'] = $this->generateSignRigorous($params,2);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('YJ_withdrawalOrder',$params);
        $res = $this->requestService->postFormData(self::$url_cashout, $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('YJ_withdrawalOrder',$res);
        if ($res['code'] != 0) {
            $this->_msg = $res['msg'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['data']['orderNo'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('YJpay_withdrawalCallback',$request->post());
        $data = $request->post();
        $pay_status = 0;
        $status = (int)$data['code'];
        if($status == 0){
            $pay_status= 1;
        }
        if($status == -1){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'YJpay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名

        $params = $data['data'];
        if(!is_array($params))$params = json_decode($params,true);
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSignRigorous($params,2) <> $sign) {
            $this->_msg = 'YJpay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $params['tradeNo'],
            'plat_order_id' => $params['orderNo'],
            'pay_status' => $pay_status
        ];
        return $where;
    }

}
