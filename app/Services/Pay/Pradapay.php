<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Pradapay extends PayStrategy
{

    protected static $url = 'http://gateway.pradapay.com/';    // 支付网关

    protected static $url_cashout = 'http://gateway.pradapay.com/'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'pradapay';   // 支付公司名

    public $amountFiled = 'orderAmt';

    // rsa公钥
    protected $rsaPublicKey = "-----BEGIN PUBLIC KEY-----
    MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAigkqmkPmNg+8Ka2FAC1qww4Cf65S3
Oq1Yq3yKL+DQLGH8CE1iW1yYaVpGK4hfB8VzhGbx9UKjC/btsFmaaG9Zp0mBqtoHzkRdXXvh+51
3NhrqOhxmh0I+uWHxoZofC259/vrgySKCnVrM3wa582hd9i0NIljHzJlh0aixZHuuD2PwdqYvpYWj3
QwNSJz7Ne8GvQqw5kKBkhu1m2pgsddsF++w+L7GT5t/rb60pCf0BqSwu+jOA1pJi9JThUyRjgmm
fDC63WIx2AYZSFUaeV8jbYnJg9rZ+nkbSgO1bY8CcVUtgngXZ2aUoCqE/ZZo1CAL2167Bo+vAKQ
w2Od++mxeQIDAQAB
-----END PUBLIC KEY-----";
    // rsa密钥
    protected $rsaSecretKey = "-----BEGIN PRIVATE KEY-----
    MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCLKC8fyXALg0XDAd8URJHVIfp/
vjgMKgcbJCtBmyXh1Bgx2FubHSTFzE7424+8kRzOELiH7rNRwuhdHN8xe1Ziuq2VnZs5+Vf85mmk
3pkWr50ucFA+5v0+3V8FlOoXB18Ezrudmvk7aQGty5PB/qgy5IMKGRRjCg0hD3/jH/+GZwDhDrSn
ygnkny91a740M6SOFPHd40VJIz2hSqumzAI4yngO5lBJxXo1ADQ9I5tXHCLWQ4CjFKa0eiRpVpElZ
qyttShiwTVAtgLPew1kOLQq36tc5qEFwjuoRLliNIf2q4StQizGO2LbVBesmf90uQ8gykxTt49ZBrtu7U
zdJ7TJAgMBAAECggEATnjZoiY5DfT3+RDsGITWbAceOL0u5AUrPq31yqhQA3pULn+goOcdXoFpI0
LFWoPGZ5ncRaNg8cFkwWVPo5q2yzQUQSPAbj/i01Wny3ZPhBCCJbbmOaKOus2hEQe5vkTE23Qt
LqI+27bKYJRFfYc6mIBuONqNuY/oeXVBG/ZmZQaJcSm8HKPaeJAlHmhAyS0cgJtPa1C9KspOLxPd
VdRKX9QjRm7KteMYWcK91EX8VNMX6APSWi/ouxhISOPJJ3f/Wr1vaGI0FOWbcDmVsPtvjzM9RCf
QQhfer0Sp3sV1v7mttQHsJ1/7D+1vnmez3V7IxDhvzqqvwuKPijB7IUMUgQKBgQDLHsE1RfDtgEp
qfsJWm2dGhAQBxDgVU43oYU+ITBFQ2whRiornyIet/5bNwJxtXXRnYV4GFmD2/PxkjTyKkADyP5T
vfb/e9RBS8k4k+kWvnzNxGuVzP+z93FTlMEwOnR2mv5FRI3PbXYrCyc+ylTTg705zygkhs7w3qxUU
vtWb2QKBgQCvYoIpU20nQCIpxJPBrdtk5+kbjkTvXJWG8sJCBbCAeZEO7T6pCelut2G98HdMfWVz
L9KSjS9aqRVA5OfFLy6VeD2eiQSMSJn8KKUD4d47rEXA8W32x7ysR+R9DPihi7Wam4FC+i0RlMRF
Z7zBlcnBBG3qX8GEHOBEEsGprIz6cQKBgD5x8gwiuHMLodUjqzNdC18ObvzsCiHkUhhC8mSAnkIx
VFldMl8Xsz62+PHAaVbmCEdQE8vjTWQhWqa/FQG0S9Yt3efSzQ4KYT5e589OceYQF4yKLEhGOu
HAvwjG1FsZymfdNRuwvomONH619Hh+jocoiwHl9vC5hP/IRO7fzGPhAoGBAIh+1ShOimv6yNvS5
t/cbBxLNSvB/LqBRspBEpiJjwVeF1wTnim20hrd088cX+yCxzrvZCW0hb88SpM3032uK8YeT26b2pN
HSbhq9Ypg9jFg8OSpwVhHuPon9ZaxSquHbO4HyoES7ZJ6Qop1ovzSk7OJu/WlUpl8U7oHEvv3k7
yxAoGAO6qplrQkMvPjAvJylOhNlg2wKwmH7nKoUTqjITBtTu9tcrWJID5i8+R+PWPMcEyzAnt5sZqt
KQyH2Gnp1zs81FpdDEb4yQQL18OMG/obWfX2hoTNKZit6YvhGkxog12gQNBKNyol4hpmdBvd0
xzjS64SbNYDVd6HK/F1zxhawR4=
-----END PRIVATE KEY-----";

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
        $this->recharge_callback_url = self::$url_callback . '/api/recharge_callback';
        $this->withdrawal_callback_url =  self::$url_callback . '/api/withdrawal_callback' . '?type='.$this->company;
    }

    protected $rechargeTypeList = [
        '1' => '100103',
        '2' => 'MoMoPay',
        '3' => 'ZaloPay',
        '4' => '100104',
        '5' => '100101',
        '6' => '100102',
    ];

    protected $banks = [
        'VIB' => [
            'bankId' => 115,
            'bankName' => 'VIB'
        ],
        'VPBank' => [
            'bankId' => 128,
            'bankName' => 'VPB'
        ],
        'BIDV' => [
            'bankId' => 121,
            'bankName' => 'BIDV'
        ],
        'VietinBank' => [
            'bankId' => 121,
            'bankName' => 'VTB'
        ],
        'SHB' => [
            'bankId' => 133,
            'bankName' => 'SHB'
        ],
        'ABBANK' => [
            'bankId' => 137,
            'bankName' => 'ABB-K'
        ],
        'AGRIBANK' => [
            'bankId' => 131,
            'bankName' => 'AGR'
        ],
        'Vietcombank' => [
            'bankId' => 117,
            'bankName' => 'VCB'
        ],
        'Techcom' => [
            'bankId' => 115,
            'bankName' => 'TCB'
        ],
        'ACB' => [
            'bankId' => 118,
            'bankName' => 'ACB'
        ],
        'SCB' => [
            'bankId' => 147,
            'bankName' => 'SCB'
        ],
        'MBBANK' => [
            'bankId' => 129,
            'bankName' => 'MB'
        ],
        'EIB' => [
            'bankId' => 122,
            'bankName' => 'EIB'
        ],
        'STB' => [
            'bankId' => 10000,
            'bankName' => 'STB'
        ],
        'DongABank' => [
            'bankId' => 145,
            'bankName' => 'OCB'
        ],
        'GPBank' => [
            'bankId' => 970408,
            'bankName' => 'GPB'
        ],
        'Saigonbank' => [
            'bankId' => 148,
            'bankName' => 'SGB'
        ],
        'PGBank' => [
            'bankId' => 152,
            'bankName' => 'PGB'
        ],
        'Oceanbank' => [
            'bankId' => 970414,
            'bankName' => 'OJB'
        ],
        'NamABank' => [
            'bankId' => 142,
            'bankName' => 'NAB'
        ],
        'TPB' => [
            'bankId' => 130,
            'bankName' => 'TPB'
        ],
        'HDB' => [
            'bankId' => 144,
            'bankName' => 'HDB'
        ],
        'VAB' => [
            'bankId' => 149,
            'bankName' => 'VAB'
        ],
    ];

    public function generateSignRigorous(array $params, $type=1){
        $md5Str = $this->getMd5Str($params, $type);
        return $this->rsaEncrypt($md5Str);
    }

    public function verifySignRigorous($params, $sign, $type=1)
    {
        $md5Str = $this->getMd5Str($params, $type);
        ##rsa验证
        return $this->rsaVerify($sign);
    }

    protected function getMd5Str($params, $type=1)
    {
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if(!empty($value))
                $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $secretKey;
        \Illuminate\Support\Facades\Log::channel('mytest')->info('prada_rechargeOrder_signStr', [$sign]);
        $md5Str =  strtoupper(md5($sign));
        return $md5Str;
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'merId' => $this->rechargeMerchantID,
            'orderId' => $order_no,
            'orderAmt' => intval($money),
            'channel' => 'test',
            'desc' => 'recharge balance',
            'mob' => 8888888888,
            'email' => '11111111@email.com',
            'ip' => getIp(),
            'notifyUrl' => $this->recharge_callback_url,
            'returnUrl' => env('SHARE_URL',''),
            'nonceStr' => $this->makeNonce(),
        ];
        $params['sign'] = $this->generateSignRigorous($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('prada_rechargeOrder', [$params]);

        $res = $this->requestService->postFormData(self::$url . 'pay' , $params, [
            "content-type" => "application/x-www-form-urlencoded",
        ]);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('prada_rechargeOrder_return', [$res]);
        if ($res['code'] != 1) {
            $this->_msg = $res['msg'];
            return false;
        }
        $native_url = $res['data']['payurl'];

        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['data']['sysorderno'],
            'verify_money' => '',
            'match_code' => '',
            'is_post' => $is_post ?? 0
        ];
        return $resData;
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('prada_rechargeCallback',$request->post());

        if ($request->status != 1)  {
            $this->_msg = 'VNMTB-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if (!$this->verifySignRigorous($params,$sign,1)) {
            $this->_msg = 'VNMTB-签名错误';
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

        // 1 银行卡 2 Paytm 3代付
//        $pay_type = 3;
        $money = $withdrawalRecord->payment;    // 打款金额
//        $ip = $this->request->ip();
//        $order_no = self::onlyosn();
        $order_no = $withdrawalRecord->order_no;
        $bank = $this->banks[$withdrawalRecord->bank_name] ?? '';
        if(!$bank)
        {
            $this->_msg = '该银行卡不支持提现,请换一张银行卡';
            return false;
        }
        $params = [
            'mer_no' => $this->withdrawMerchantID,
            'mer_order_no' => $order_no,
            'acc_no' => $withdrawalRecord->bank_number,
            'acc_name' => $withdrawalRecord->account_holder,
            'ccy_no' => 'VND',
            'order_amount' => intval($money),
            'bank_code' => $bank['bankName'],
            'summary' => 'Balance Withdrawal',
//            'province' => $withdrawalRecord->ifsc_code,
            'notifyUrl' => $this->withdrawal_callback_url
        ];
        $params['sign'] = $this->generateSignRigorous($params,2);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('VNMTBpay_withdrawalOrder',$params);
        $res = $this->requestService->postJsonData(self::$url_cashout . 'withdraw/singleOrder', $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('VNMTBpay_withdrawalOrder',$res);
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('VNMTBpay_withdrawalCallback',$request->post());

        $pay_status = 0;
        $status = (string)($request->status);
        if($status == 'SUCCESS'){
            $pay_status= 1;
        }
        if($status == 'FAIL'){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'VNMTBpay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSignRigorous($params,2) <> $sign) {
            $this->_msg = 'VNMTBpay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->mer_order_no,
            'plat_order_id' => $request->order_no,
            'pay_status' => $pay_status
        ];
        return $where;
    }

    protected function makeNonce():string
    {
        return date("YmdHis") . mt_rand(100000,999999);
    }

    public function rsaEncrypt($data)
    {
        /* 从PEM文件中提取私钥 */
        $res = openssl_pkey_get_private($this->rsaSecretKey);
        /* 对数据进行签名 */
        openssl_sign($data, $sign, $res,OPENSSL_ALGO_SHA256 );
        /* 释放资源 */
        openssl_free_key($res);
        /* 对签名进行Base64编码，变为可读的字符串 */
        $sign = base64_encode($sign);
        return $sign;
    }

    protected function rsaVerify($sign):bool
    {
        $sign = base64_decode($sign);
        //验证公钥是否可用
        $pu = openssl_get_publickey($this->rsaPublicKey);
        $returnArray = '';
        //公钥验证
        $isSuccess = (bool)openssl_verify($returnArray,$sign,$pu,OPENSSL_ALGO_SHA256);
        return $isSuccess;
    }

}
