<?php


namespace App\Services\Pay;

use App\Libs\Aes;
use App\Repositories\Api\UserRepository;
use App\Services\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 *  如：unicasino.in  的充值和提现类
 */
class Matthew extends PayStrategy
{

    protected static $rechargeUrl = 'https://payapitest.soon-ex.com/';

    protected static $withdrawUrl = 'https://payapitest.soon-ex.com/';

    protected static $nativeUrl = 'http://paytest.soon-ex.com/#/?orderId=';

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'matthew';   // 支付公司名

    protected $iv = '!WFNZFU_{H%M(S|a';

    public $rechargeRtn='success'; //支付成功的返回
    public $withdrawRtn='success'; //提现成功的返回

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
     * 生成签名   sign = Md5(key1=vaIue1&key2=vaIue2…商户密钥);
     */
    public function generateSign(array $params)
    {
        sort($params,SORT_LOCALE_STRING);
        $string = '';
        foreach ($params as $key => $value) {
            $string .= $value;
        }
        return strtolower(sha1($string));
    }

    /**
     * 充值下单接口
     */
    function rechargeOrder($pay_type,$money)
    {
        $order_no = self::onlyosn();

        $en = [
            'amount' => (string)$money,
            'thirdOrderNumber' => $order_no,//商家自己平台的订单号
            'thirdUserId' => $this->getUserId(),//商家自己平台的会员ID，如果没有可以用上面的订单号
        ];
        $header = [
            'Content-Type: application/json',
            'Authorization: Basic'.base64_encode('60371540'.':'.'02f32e264e6c6decc113e90844844422'), //添加头，在name和pass处填写对应账号密码
            'Content-Length: ' . strlen(json_encode($en))
        ];
        \Illuminate\Support\Facades\Log::channel('mytest')->info('matthew_rechargeOrder', $header);
//        $Aes = new Aes();
//        $key = substr($this->rechargeSecretkey, 0,16);
//        $encryptedData = $Aes->encryptWithOpenssl($key, $en, $this->iv);
//        $data = [
//            'encryptedData' => $encryptedData,   //openssl_encrypt进行aes对称加密
//            'signaturePo' => [
//                'apiId' => $this->rechargeMerchantID,  //商家ID
//                'nonce' => (string)randomStr(10),
//                'signature' => '',
//                'timestamp' => get_total_millisecond()
//            ],
//        ];
//        $signature = $this->generateSign([   //调用签名函数进行数据签名
//            $data['signaturePo']['timestamp'].'',
//            $data['signaturePo']['nonce'].'',
//            $data['signaturePo']['apiId'],
//            $this->rechargeSecretkey,
//            json_encode($en),  //将数组转json格式的数据
//        ]);
//        $data['signaturePo']['signature'] = $signature;

        \Illuminate\Support\Facades\Log::channel('mytest')->info('matthew_rechargeOrder', $en);

//        $res = $this->requestService->postJsonData(self::$rechargeUrl . 'otc/api/getRechargeData', $en, $header);
        $res = $this->curlhead(self::$rechargeUrl . 'otc/api/getRechargeData', $en, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('matthew_rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = 'prepare recharge failed';
            return false;
        }
        if ($res['code'] <> 0) {
            $this->_msg = $res['message'];
            return false;
        }
        $resData = [
            'out_trade_no' => $order_no,
            'shop_id' => $this->rechargeMerchantID,
            'pay_company' => $this->company,
            'pay_type' => $pay_type,
            'native_url' => self::$nativeUrl . $res['data']['orderNumber'],
            'pltf_order_id' => '',
            'verify_money' => $en['amount'],
            'match_code' => '',
            'notify_url' => $this->recharge_callback_url,
            'params' => [],
            'is_post' => 0,
        ];
        return $resData;
    }

    public  function curlhead($url,$params,$header){  //请求参数与URL post数据组装
        $data_string = json_encode($params); //将数组转json格式的数据
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $hdresult = curl_exec($ch);
        return $hdresult;
    }

    function withdrawalOrder(object $withdrawalRecord)
    {
        $money = $withdrawalRecord->payment;    // 打款金额
        $order_no = $withdrawalRecord->order_no;

        $en = [
            'amount' => (string)$money,
            'thirdOrderNumber' => $order_no,//uniqid(),商家自己平台的提现订单号
            'thirdUserId' => $this->getUserId(), //商家自己平台的会员ID，如果没有可以用上面的订单号
            'issuePayPo'=>[
                'accountName' => $withdrawalRecord->bank_number,  //提现用户收款的账户
                'ifscCode'=> $withdrawalRecord->ifsc_code, //提现用户的IFS code
                'bankName' => $withdrawalRecord->bank_name,  //银行名
                'name' => $withdrawalRecord->account_holder,  //提现用户姓名
                'paymentId'=>'11' //收款方式ID这里以IMPS为例
            ]
        ];
        $Aes = new Aes();
        $key = substr($this->withdrawSecretkey, 0,16);
        $data = [
            'encryptedData' => $Aes->encryptWithOpenssl($key, $en, $this->iv),   //提现数据加密
            'signaturePo' => [
                'apiId' => $this->withdrawMerchantID,
                'nonce' => (string)randomStr(10).'',
                "apisecret" => $this->withdrawSecretkey,
                'signature' => '',
                'timestamp' => get_total_millisecond()
            ],
        ];
        $signature = $this->generateSign([   //调用签名函数进行数据签名
            $data['signaturePo']['timestamp'].'',
            $data['signaturePo']['nonce'].'',
            $data['signaturePo']['apiId'],
            $this->withdrawSecretkey,
            json_encode($en), //将数组转json格式的数据
        ]);
        $data['signaturePo']['signature'] = $signature;

        \Illuminate\Support\Facades\Log::channel('mytest')->info('matthew_withdrawalOrder',$data);
        $res = $this->requestService->postJsonData(self::$withdrawUrl . 'otc/api/issue', $data);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('matthew_withdrawalOrder_rtn',[$res]);
        if(!$res){
            $this->_msg = 'request payout failed';
            return false;
        }
        if($res['code'] != 0){
            $this->_msg = $res['message'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['orderNumber'],
            'order_no' => $order_no
        ];
    }

    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('matthew_rechargeCallback',$request->input());

        if(!isset($request->encryptedData)){
            $this->_msg = 'matthew-recharge-交易未完成.';
            return false;
        }
        $Aes = new Aes();
        $key = substr($this->rechargeSecretkey, 0,16);
        $data = $Aes->decryptWithOpenssl($key, $request->encryptedData, $this->iv);   //提现数据加密
        $data = json_decode($data,true);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('matthew_rechargeCallback_decryptWithOpenssl',[$data]);
        if ($data['status'] != '1')  {
            $this->_msg = 'matthew-recharge-交易未完成';
            return false;
        }
        $where = [
            'order_no' => $data['thirdOrderNumber'],
        ];
        $this->amount = $data['amount'];
        return $where;
    }

    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('sepro_withdrawalCallback',$request->post());
        if(!isset($request->encryptedData)){
            $this->_msg = 'matthew-withdraw-交易未完成.';
            return false;
        }
        $Aes = new Aes();
        $key = substr($this->withdrawSecretkey, 0,16);
        $data = $Aes->decryptWithOpenssl($key, $request->encryptedData, $this->iv);   //提现数据加密
        $data = json_decode($data,true);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('matthew_withdrawCallback_decryptWithOpenssl',[$data]);
        $pay_status = 0;
        $status = (string)($data['status']);
        if($status == 1){
            $pay_status= 1;
        }
        if($status == 0 || $status == 2){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'matthew_withdrawal-交易未完成';
            return false;
        }

        $where = [
            'order_no' => $data['thirdOrderNumber'],
            'plat_order_id' => '',
            'pay_status' => $pay_status
        ];
        return $where;
    }
}

