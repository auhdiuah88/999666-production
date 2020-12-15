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

        $res = $this->requestService->postJsonData(self::$url . '/ty/orderPay' , $params);
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
