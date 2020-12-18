<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
class MTBpay extends PayStrategy
{

    protected static $url = 'http://lrznvm.fakgt.com/';    // 支付网关

    protected static $url_cashout = 'http://sujary.fakgt.com/'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $merchantID;
    public $secretkey;
    public $company = 'MTBpay';   // 支付公司名

    public function _initialize()
    {
        $this->merchantID = config('pay.company.'.$this->company.'.merchant_id');
        $this->secretkey = config('pay.company.'.$this->company.'.secret_key');

        $this->recharge_callback_url = self::$url_callback . '/api/recharge_callback' . '?type='.$this->company;
        $this->withdrawal_callback_url =  self::$url_callback . '/api/withdrawal_callback' . '?type='.$this->company;
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

    public function generateSignRigorous(array $params){
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if($value)
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
        $params = [
            'mer_no' => $this->merchantID,
            'mer_order_no' => $order_no,
            'pname' => 'ZhangSan',
            'pemail' => '279890363@qq.com',
            'phone' => '15983587793',
            'order_amount' => $money,
            'countryCode' => 'IND',
            'ccy_no' => 'INR',
            'busi_code' => 'UPI',
            'goods' => 'recharge balance',
            'notifyUrl' => $this->recharge_callback_url,
        ];
        $params['sign'] = $this->generateSign($params);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('MTB_rechargeOrder', [$params]);

        $res = $this->requestService->postJsonData(self::$url . 'ty/orderPay' , $params);
        if ($res['status'] != 'SUCCESS') {
            \Illuminate\Support\Facades\Log::channel('mytest')->info('MTB_rechargeOrder_return', $res);
            $this->_msg = $res['err_msg'];
            return false;
        }
        $native_url = $res['order_data'];
        if(strpos($native_url,"POST;") == 0){
            $native_url = str_replace('POST;','',$native_url);
            $is_post = 1;
        }
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => '',
            'verify_money' => '',
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('MTB_rechargeCallback',$request->post());

        if ($request->status != 'SUCCESS')  {
            $this->_msg = 'MTB-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSignRigorous($params) <> $sign) {
            $this->_msg = 'MTB-签名错误';
            return false;
        }

        $where = [
            'order_no' => $request->mer_order_no,
        ];
        return $where;
    }

    /**
     *  后台审核请求提现订单 (提款)  代付方式
     */
    public function withdrawalOrder(object $withdrawalRecord)
    {

        // 1 银行卡 2 Paytm 3代付
//        $pay_type = 3;
        $money = $withdrawalRecord->payment;    // 打款金额
//        $ip = $this->request->ip();
//        $order_no = self::onlyosn();
        $order_no = $withdrawalRecord->order_no;
        $params = [
            'mer_no' => $this->merchantID,
            'mer_order_no' => $order_no,
            'acc_no' => $withdrawalRecord->bank_number,
            'acc_name' => $withdrawalRecord->account_holder,
            'ccy_no' => 'IND',
            'order_amount' => $money,
            'bank_code' => $withdrawalRecord->mtb_code,
            'summary' => '余额充值',
            'province' => $withdrawalRecord->ifsc_code
        ];
        $params['sign'] = $this->generateSign($params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('MTBpay_withdrawalOrder',$params);
        $res = $this->requestService->postJsonData(self::$url_cashout . 'withdraw/singleOrder', $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('MTBpay_withdrawalOrder',$res);
        if ($res['status'] != 'SUCCESS') {
            $this->_msg = $res['err_msg'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['order_no'],
            'order_no' => $order_no
        ];
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
        $params['sign'] = $this->generateSign($params);
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
