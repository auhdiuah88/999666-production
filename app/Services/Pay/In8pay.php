<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class In8pay extends PayStrategy
{

    protected static $url = 'https://indiayh.com/api/';    // 网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'in8pay';   // 支付公司名

    protected $blackParams = ['merchant_sn'];

    // rsa公钥
    protected $rsaPublicKey = "-----BEGIN PUBLIC KEY-----
    MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC5iVJXx1YX/6dtPhxHBSs1r08U
YW9NjnRTf/1cJIBp46PWSFBzngvYcOukclsl0vv+njeKVgaDXtDz5FiEt4ajBbEk
jVMO8sYFKU0qoWRE2GNsVobXPQ5BO/JeE6mgJTd3zqo1Q5X6aG0PrW7kwM9S4umt
T0n4yTG/6UH9NhbxMwIDAQA
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
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $secretKey;
        return strtolower(md5($sign));
    }

    public function generateSignRigorous(array $params, $type=1){
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if($value)
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
            'merchant_sn' => $this->rechargeMerchantID,
            'down_sn' => $order_no,
            'amount' => intval($money * 100),
            'notify_url' => $this->recharge_callback_url,
            'channel_code' => 1007,
            'pay_type' => 7
        ];
        $params['sign'] = $this->generateSign($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('in8pay_rechargeOrder', [$params]);
        $res = $this->requestService->postFormData(self::$url . 'trans/pay', $params, [
            "content-type" => "application/x-www-form-urlencoded",
            "charset" => "UTF-8"
        ]);
//        $res = $this->requestService->postJsonData(self::$url . 'trans/pay' , $params,[
//            "content-type" => "application/x-www-form-urlencoded",
//            "charset" => "UTF-8"
//        ]);
        if ($res['code'] != "0") {
            \Illuminate\Support\Facades\Log::channel('mytest')->info('in8pay_rechargeOrder_return', $res);
            $this->_msg = $res['msg'];
            return false;
        }
        $native_url = $res['pay_url'];
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['trans_sn'],
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('in8pay_rechargeCallback',$request->post());

        if ($request->status != "0")  {
            $this->_msg = 'in8pay-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        unset($params['code']);
        unset($params['msg']);
        if ($this->generateSignRigorous($params,1) <> $sign) {
            $this->_msg = 'in8pay-签名错误';
            return false;
        }

        $where = [
            'order_no' => $request->down_sn,
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
            'down_sn' => $withdrawalRecord->order_no,
            'amount' => (int)($money * 100),
            'area' => 1,
            'bank_account' => $withdrawalRecord->account_holder,
            'bank_cardno' => $withdrawalRecord->bank_number,
            'bank_code' => $withdrawalRecord->ifsc_code,
            'channel_code' => 1007,
            'mobile' => $withdrawalRecord->phone,
            'notify_url' => $this->withdrawal_callback_url
        ];
        $params['sign'] = $this->generateSign($params,2);
        $cipher_data = $this->rsaEncrypt($params);
        $merchant_sn = $this->withdrawMerchantID;
        \Illuminate\Support\Facades\Log::channel('mytest')->info('in8pay_withdrawalOrder',$params);
        $res = $this->requestService->postFormData(self::$url . 'settle/pay', compact('merchant_sn','cipher_data'),[
            "content-type" => "application/x-www-form-urlencoded",
            "charset" => "UTF-8"
        ]);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('in8pay_withdrawalOrder2_res',$res);
        if ($res['statusCode'] != '0') {
            $this->_msg = $res['msg'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['settle_sn'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('in8pay_withdrawalCallback',$request->post());

        if ($request->status != 1) {
            $this->_msg = 'in8pay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['code']);
        unset($params['msg']);
        if ($this->generateSignRigorous($params,2) <> $sign) {
            $this->_msg = 'in8pay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->down_sn,
            'plat_order_id' => $request->settle_sn,
            'payment' => bcdiv($request->payment,100),
            'service_charge' => bcdiv($request->fee_fix,100),
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
