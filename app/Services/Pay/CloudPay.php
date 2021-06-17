<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CloudPay extends PayStrategy
{

    protected static $url = 'https://ydfap.ggzy88.com/api/merchant/pay';    // 支付网关

    protected static $url_cashout = 'https://ydfap.ggzy88.com/api/merchant/behalf_pay'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'cloudpay';   // 支付公司名
    public $rechargeRtn = ['resultStatus'=>200];
    public $withdrawRtn = ['resultStatus'=>200];

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
        return md5(md5(md5($sign)));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'app_id' => $this->rechargeMerchantID,
            'out_trade_no' => $order_no,
            'type' => 1,
            'notify_url' => $this->recharge_callback_url,
            'user_phone' =>  '18888888888',
            'user_ip' => request()->ip(),
            'money' => intval($money),
        ];
        $params['sign'] = $this->generateSign($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('cloudPay_rechargeOrder', [$params]);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        $res =dopost(self::$url, $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('MTB_rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "prepay failed";
            return false;
        }
        if ($res['resultCode'] != 200) {
            $this->_msg = $res['resultMsg'];
            return false;
        }
        $native_url = $res['data']['payUrl'];

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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('cloudPay_rechargeCallback',$request->input());

        if ($request->resultCode != 1001)  {
            $this->_msg = 'cloudPay-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,1) <> $sign) {
            $this->_msg = 'cloudPay-签名错误';
            return false;
        }
        $this->amount = $params['orderMoney'];
        $where = [
            'order_no' => $request->out_trade_no,
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
            'app_id' => $this->withdrawMerchantID,
            'out_trade_no' => $order_no,
            'type' => 2,
            'notify_url' => $this->withdrawal_callback_url,
            'bank_card_no' => $withdrawalRecord->bank_number,
            'bank_name' => $withdrawalRecord->bank_name,
            'bank_code' => $withdrawalRecord->ifsc_code,
            'bank_account' => $withdrawalRecord->account_holder,
            'money' => intval($money),
        ];
        $params['sign'] = $this->generateSign($params,2);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('cloudPay_withdrawalOrder',$params);
        $res =dopost(self::$url_cashout, $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('cloudPay_withdrawalOrder',[$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['resultCode'] != 200) {
            $this->_msg = $res['resultMsg'];
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('cloudPay_withdrawalCallback',$request->input());

        $pay_status = 0;
        $status = (int)($request->resultCode);
        if($status == 1001){
            $pay_status= 1;
        }
        if($status == 1002){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'cloudPay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'cloudPay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->out_trade_no,
            'plat_order_id' => $request->orderNum,
            'pay_status' => $pay_status
        ];
        return $where;
    }

}
