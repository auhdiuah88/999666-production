<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Richpay extends PayStrategy
{

    protected static $url = 'http://api.tshop.live/order/';    // 网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'richpay';   // 支付公司名

    protected $blackParams = ['merchant_sn'];

    // rsa公钥
    protected $rsaPublicKey = "-----BEGIN PUBLIC KEY-----
    MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC5iVJXx1YX/6dtPhxHBSs1r08U
YW9NjnRTf/1cJIBp46PWSFBzngvYcOukclsl0vv+njeKVgaDXtDz5FiEt4ajBbEk
jVMO8sYFKU0qoWRE2GNsVobXPQ5BO/JeE6mgJTd3zqo1Q5X6aG0PrW7kwM9S4umt
T0n4yTG/6UH9NhbxMwIDAQAB
-----END PUBLIC KEY-----";
    // rsa密钥
    protected $rsaSecretKey = "ea2fe6fc046a7e6db3f34a9212c2a48d";

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
    public  function generateSign(array $params, $type=1)
    {
        if(!isset($params['channleOid']))$params['channleOid'] = $params['channelOid'];
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        $sign = $params['channelId'] . $params['channleOid'] . $params['amount'] . $secretKey;
        return md5($sign);
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'amount' => (string)$money,
//            'callbackUrl' => $this->recharge_callback_url,
            'channelId' => (string)($this->rechargeMerchantID),
            'channleOid' => (string)$order_no,
//            'email' => '88888888@in.com',
            'email' => '',
            'firstName' => 'Customer',
//            'firstName' => '',
            'mobile' => '',
//            'mobile' => '88888888',
            'notifyUrl' => $this->recharge_callback_url,
            'payType' => 1,
            'remark' => 'recharge',
            'timestamp' => time() * 1000,  //精确到毫秒
        ];
        $params['sign'] = $this->generateSign($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('richpay_rechargeOrder', [$params]);
        $res = $this->requestService->postJsonData(self::$url . 'order/submit', $params);
        if ($res['code'] != "0000") {
            \Illuminate\Support\Facades\Log::channel('mytest')->info('richpay_rechargeOrder_return', $res);
            $this->_msg = $res['message'];
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('richpay_rechargeCallback',$request->post());
        if ($request->status != 1)  {
            $this->_msg = 'richpay-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        if ($this->generateSign($params,1) <> $sign) {
            $this->_msg = 'richpay-签名错误';
            return false;
        }

        $where = [
            'order_no' => $request->channleOid,
            'pltf_order_id' => $request->oid
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
            'amount' => (string)$money,
            'channelId' => (string)$this->withdrawMerchantID,
            'channelOid' => (string)$order_no,
            'fundAccount' => [
                'accountType' => 'bank_account',
                'bankAccount' => [
                    'accountNumber' => (string)$withdrawalRecord->bank_number,
                    'ifsc' => (string)$withdrawalRecord->ifsc_code,
                    'name' => (string)$withdrawalRecord->account_holder
                ],
            ],
            'mode' => 'upi',
            'notifyUrl' => $this->withdrawal_callback_url,
            'timestamp' => time() * 1000,
        ];
        \Illuminate\Support\Facades\Log::channel('mytest')->info('richpay_withdrawalOrder_test',$params);
        $params['sign'] = $this->generateSign($params,2);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('richpay_withdrawalOrder',$params);
        $res = $this->requestService->postJsonData(self::$url . 'order/payout/submit', $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('richpay_withdrawalOrder2_res',$res);
        if ($res['code'] != '0000') {
            $this->_msg = $res['message'];
            return false;
        }
        if (in_array($res['data']['state'], [2,3])) {
            $this->_msg = $res['data']['msg'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['data']['payOutId'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('richpay_withdrawalCallback',$request->input());

        if ($request->status != 1) {
            $this->_msg = 'richpay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'richpay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->channleOid,
            'plat_order_id' => $request->oid,
        ];
        return $where;
    }

    protected function rsaEncrypt($params)
    {
        $params = $this->filterBlackParams($params);
        $originalData = json_encode($params);
        $crypto = '';
        $encryptData = '';
        foreach (str_split($originalData, 117) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $this->rsaPublicKey);
            $crypto .= $encryptData;
        }
        return base64_encode($crypto);
    }

    protected function filterBlackParams($params)
    {
        foreach ($this->blackParams as $item){
            if(in_array($item, $params))
                unset($params[$item]);
        }
        return $params;
    }

}
