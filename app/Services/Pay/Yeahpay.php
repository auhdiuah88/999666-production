<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class Yeahpay extends PayStrategy
{

    protected static $url_oauth = 'http://testapi.yeahpay.in/gpauth/oauth/token';    // access_token网关

    protected static $url = 'http://testapi.yeahpay.in/core/api/payment/prepay';    // 支付网关

    protected static $url_cashout = 'https://usdt1788.in/center/api/payout.do'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'Yeahpay';   // 支付公司名

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

    protected $rechargeTypeList = [
        '1' => 3,
        '2' => 2,
        '3' => 1,
    ];

    /**
     * 生成签名  sign = Md5(key1=vaIue1&key2=vaIue2&key=签名密钥);
     */
    public function generateSignRigorous(array $params, $type=1){
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $str = "";
        foreach ($params as $v => $k) {
            $str = $str . $k;
        }
        $str = $str . $secretKey;

        $hash = hash_hmac('sha256', $str, $secretKey);
        return strtoupper($hash);
    }

    public function auth($flag=1)
    {
        $key = "YEAH_PAY_ACCESS_{$flag}";
        ##现判断缓存中是否有access_token
        if(Redis::exists($key)){
            $data = json_decode(Redis::get($key),true);
            return $data['access_token'];
        }

        $app_id = $flag == 1? $this->rechargeMerchantID : $this->withdrawMerchantID;
        $app_key = $flag == 1? $this->rechargeSecretkey : $this->withdrawSecretkey;

        $params = [
            "grant_type" => "client_credentials"
        ];
        $accessToken = base64_encode($app_id . ":" . $app_key);
        $res = $this->requestService->postFormData(self::$url_oauth,$params, [
            'Authorization: Basic' => $accessToken
        ]);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Yeah_rechargeOrder', [$res]);
        if($res && isset($res['access_token'])){
            Redis::set($key, json_encode($res,JSON_UNESCAPED_UNICODE));
            Redis::expire($key, $res['expires_in'] - 600);
            return $res['access_token'];
        }
        return false;
    }

    protected function getSign($params, $access_token,$flag=1)
    {
        $data = [
            'key' => $flag == 1? $this->rechargeSecretkey : $this->withdrawSecretkey,
            'params' => $params,
            ''
        ];
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $access_token = $this->auth();
        if(!$access_token){
            $this->_msg = "get access_token failed";
            return false;
        }
        $order_no = self::onlyosn();
        $params = [
            'amount' => (string)$money,
            'merchantID' => $this->rechargeMerchantID,
            'payType' => 6,
            'merchantOrderId' => $order_no,
            'productName' => "recharge balance",
            'productDescription' => "customer recharge balance",
            'merchantUserId' => request()->get('userInfo')['id'],
            'merchantUserName' => request()->get('userInfo')['phone'],
            'merchantUserIp' => getIp(),
            'countryCode' => "IN",
            'currency' => "INR",
            'redirectUrl' => env('SHARE_URL',''),
        ];
        $ext = [
            'addrCity' => 'Meban',
            'addrStreet' => 'hdggd-jshhs',
            'addrNumber' => '154242',
        ];
        $params['sign'] = $this->generateSignRigorous($params,1);
        $params['ext'] = $ext;

        \Illuminate\Support\Facades\Log::channel('mytest')->info('Yeah_rechargeOrder', [$params]);

        $res = $this->requestService->postJsonData(self::$url, $params, [
            'Authorization' => "Bearer " . $access_token
        ]);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Yeah_rechargeOrder_return', $res);
        if ($res['errorCode'] != 1000) {
            \Illuminate\Support\Facades\Log::channel('mytest')->info('Yeah_rechargeOrder_return', $res);
            $this->_msg = "prepay failed";
            return false;
        }
        if(!!$res['orderPaymentLoad']['hadCheckPage']){
            $this->_msg = "empty checkPageUrl";
            return false;
        }
        $native_url = $res['orderPaymentLoad']['checkPageUrl'];
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['orderPaymentLoad']['channelOrderId'],
            'verify_money' => $res['orderPaymentLoad']['amount'],
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
            \Illuminate\Support\Facades\Log::channel('mytest')->info('Yeah_rechargeCallback',$request->input());
            $data = $request->input();
            if ((int)$data['code'] != 0)  {
                $this->_msg = 'Yeah-recharge-交易未完成';
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
                $this->_msg = 'Yeah-签名错误';
                return false;
            }
            $where = [
                'order_no' => $params['tradeNo'],
            ];
            return $where;
        }catch(\Exception $e){
            \Illuminate\Support\Facades\Log::channel('mytest')->info('err_Yeah_rechargeCallback',[$request->input(), $e->getMessage(), $e->getLine()]);
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Yeah_withdrawalOrder',$params);
        $res = $this->requestService->postFormData(self::$url_cashout, $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Yeah_withdrawalOrder',$res);
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Yeahpay_withdrawalCallback',$request->post());
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
            $this->_msg = 'Yeahpay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名

        $params = $data['data'];
        if(!is_array($params))$params = json_decode($params,true);
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSignRigorous($params,2) <> $sign) {
            $this->_msg = 'Yeahpay-签名错误';
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
