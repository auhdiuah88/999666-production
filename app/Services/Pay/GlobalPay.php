<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalPay extends PayStrategy
{

    protected static $url = 'http://gyials.gdsua.com/';    // 支付网关

    protected static $url_cashout = 'http://njsyal.gdsua.com/'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'globalpay';   // 支付公司名

    public $withdrawRtn = "SUCCESS";
    public $rechargeRtn = "SUCCESS";

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
        '1' => '100103',
        '2' => '100106',
        '3' => '100105',
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
            'bankName' => 'CTG'
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
            'bankName' => 'Dong'
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
        'Sacombank' => [
            'bankId' => 116,
            'bankName' => 'SACOM'
        ],
    ];

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
        return md5($sign);
    }

    public function generateSignRigorous(array $params, $type=1){
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if($value != '')
                $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $secretKey;
        return md5($sign);
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'mer_no' => $this->rechargeMerchantID,
            'mer_order_no' => $order_no,
            'pname' => 'ZhangSan',
            'pemail' => '11111111@email.com',
            'phone' => 15988888888,
            'order_amount' => intval($money),
            'countryCode' => 'VNM',
            'ccy_no' => 'VND',
            'busi_code' => $this->rechargeTypeList[$this->rechargeType],
            'goods' => 'recharge balance',
            'notifyUrl' => $this->recharge_callback_url,
            'pageUrl' => env('SHARE_URL','')
        ];
        $params['sign'] = $this->generateSignRigorous($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('GlobalPay_rechargeOrder', [$params]);

        $res = $this->requestService->postJsonData(self::$url . 'ty/orderPay' , $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('GlobalPay_rechargeOrder_return', [$res]);
        if ($res['status'] != 'SUCCESS') {
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
            'is_post' => $is_post ?? 0
        ];
        return $resData;
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('GlobalPay_rechargeCallback',$request->post());

        if ($request->status != 'SUCCESS')  {
            $this->_msg = 'GlobalPay-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSignRigorous($params,1) <> $sign) {
            $this->_msg = 'GlobalPay-签名错误';
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('GlobalPay_withdrawalOrder',$params);
        $res = $this->requestService->postJsonData(self::$url_cashout . 'withdraw/singleOrder', $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('GlobalPay_withdrawalOrder',$res);
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('GlobalPay_withdrawalCallback',$request->post());

        $pay_status = 0;
        $status = (string)($request->status);
        if($status == 'SUCCESS'){
            $pay_status= 1;
        }
        if($status == 'FAIL'){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'GlobalPay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSignRigorous($params,2) <> $sign) {
            $this->_msg = 'GlobalPay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->mer_order_no,
            'plat_order_id' => $request->order_no,
            'pay_status' => $pay_status
        ];
        return $where;
    }

}
