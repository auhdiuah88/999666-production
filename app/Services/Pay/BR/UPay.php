<?php


namespace App\Services\Pay\BR;

use App\Libs\Aes;
use App\Repositories\Api\UserRepository;
use App\Services\Pay\PayStrategy;
use App\Services\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 *  如：unicasino.in  的充值和提现类
 */
class UPay extends PayStrategy
{

//    protected static $rechargeUrl = 'https://payapi.soon-ex.com/';
    protected static $rechargeUrl = 'https://payapitest.soon-ex.com/';

//    protected static $withdrawUrl = 'https://payapi.soon-ex.com/';
    protected static $withdrawUrl = 'https://payapitest.soon-ex.com/';

//    protected static $nativeUrl = 'http://pay.soon-ex.com/#/?orderId=';
    protected static $nativeUrl = 'http://paytest.soon-ex.com/brazil/#/?orderId=%s&phone=%s&mail=%s';

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'upay';   // 支付公司名

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
            'Authorization: Basic'.base64_encode($this->rechargeMerchantID.':'.$this->rechargeSecretkey), //添加头，在name和pass处填写对应账号密码
            'Content-Length: ' . strlen(json_encode($en))
        ];
        \Illuminate\Support\Facades\Log::channel('mytest')->info('upay_rechargeOrder', $en);

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
        $user = $this->getUser();
        $resData = [
            'out_trade_no' => $order_no,
            'shop_id' => $this->rechargeMerchantID,
            'pay_company' => $this->company,
            'pay_type' => $pay_type,
            'native_url' => sprintf(self::$nativeUrl, $res['data']['orderNumber'], $user->phone, ''),
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
                'accountName'=>$withdrawalRecord->ifsc_code,  //pix钥匙串
                'name'=>$withdrawalRecord->account_holder,  //提现用户姓名
                'paymentId'=>'26', //收款方式ID这里以PIX为例,其他收款方式见文件，如果填错可能会影响订单代收
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

        \Illuminate\Support\Facades\Log::channel('mytest')->info('upay_withdrawalOrder',$data);
        $res = $this->requestService->postJsonData(self::$withdrawUrl . 'otc/api/issue', $data);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('upay_withdrawalOrder_rtn',[$res]);
        if(!$res){
            $this->_msg = 'request payout failed';
            return false;
        }
        if($res['code'] != 0){
            $this->_msg = $res['message'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['data']['orderNumber'],
            'order_no' => $order_no
        ];
    }

    function rechargeCallback(Request $request)
    {

        $params = file_get_contents("php://input");
        \Illuminate\Support\Facades\Log::channel('mytest')->info('upay_rechargeCallback',[$params]);
        $params = json_decode($params,true);
        if(!$params)return false;
        if(!isset($params['encryptedData'])){
            $this->_msg = 'upay-recharge-交易未完成.';
            return false;
        }
        $Aes = new Aes();
        $key = substr($this->rechargeSecretkey, 0,16);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('upay_rechargeCallback2',[$key]);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('upay_rechargeCallback3',[$params['encryptedData']]);
        $data = $Aes->decryptWithOpenssl($key, $params['encryptedData'], $this->iv);   //提现数据加密
        $data = json_decode($data,true);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('upay_rechargeCallback_decryptWithOpenssl',[$data]);
        if ($data['status'] != '1')  {
            $this->_msg = 'upay-recharge-交易未完成';
            return false;
        }
        $where = [
            'order_no' => $data['thirdOrderNumber'],
        ];
        $this->amount = $data['amount'];
        $this->rechargeRtn = json_encode([
          "code" => 1,
          "message" => "成功",
          "success" => true
        ]);
        return $where;
    }

    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('upay_withdrawalCallback',$request->post());
        $params = file_get_contents("php://input");
        $params = json_decode($params,true);
        if(!$params)return false;
        if(!isset($params['encryptedData'])){
            $this->_msg = 'upay-withdraw-交易未完成.';
            return false;
        }
        $Aes = new Aes();
        $key = substr($this->withdrawSecretkey, 0,16);
        $data = $Aes->decryptWithOpenssl($key, $params['encryptedData'], $this->iv);   //提现数据加密
        $data = json_decode($data,true);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('upay_withdrawCallback_decryptWithOpenssl',[$data]);
        $pay_status = 0;
        $status = (string)($data['status']);
        if($status == 1){
            $pay_status= 1;
        }
        if($status == 0 || $status == 2){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'upay_withdrawal-交易未完成';
            return false;
        }

        $this->withdrawRtn = json_encode([
            "code" => 1,
            "message" => "成功",
            "success" => true
        ]);

        $where = [
            'order_no' => $data['thirdOrderNumber'],
            'plat_order_id' => '',
            'pay_status' => $pay_status
        ];
        return $where;
    }
}

