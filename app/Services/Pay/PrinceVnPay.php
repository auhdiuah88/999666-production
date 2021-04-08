<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrinceVnPay extends PayStrategy
{

    protected static $url = 'https://api.qpr200.site/pay';    // 支付网关

    protected static $url_cashout = 'https://api.qpr200.site/applyfor'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'princepay';   // 支付公司名

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

    protected $rechargeTypeList = [
        '1' => 907,
        '2' => 908,
        '3' => 921,
        '4' => 923,
    ];

    protected $banks = [
        'VIB' => [
            'bankId' => 1548,
            'bankName' => 'VIB'
        ],
        'VPBank' => [
            'bankId' => 1549,
            'bankName' => 'VPB'
        ],
        'BIDV' => [
            'bankId' => 2001,
            'bankName' => 'BIDV'
        ],
        'VietinBank' => [
            'bankId' => 2002,
            'bankName' => 'CTG'
        ],
        'SHB' => [
            'bankId' => 2003,
            'bankName' => 'SHB'
        ],
        'ABBANK' => [
            'bankId' => 2004,
            'bankName' => 'ABB-K'
        ],
        'AGRIBANK' => [
            'bankId' => 2005,
            'bankName' => 'AGR'
        ],
        'Vietcombank' => [
            'bankId' => 2006,
            'bankName' => 'VCB'
        ],
        'Techcom' => [
            'bankId' => 2007,
            'bankName' => 'TCB'
        ],
        'ACB' => [
            'bankId' => 2008,
            'bankName' => 'ACB'
        ],
        'SCB' => [
            'bankId' => 2009,
            'bankName' => 'SCB'
        ],
        'MBBANK' => [
            'bankId' => 2011,
            'bankName' => 'MB'
        ],
        'EIB' => [
            'bankId' => 2012,
            'bankName' => 'EIB'
        ],
        'STB' => [
            'bankId' => 2020,
            'bankName' => 'STB'
        ],
        'DongABank' => [
            'bankId' => 2031,
            'bankName' => 'OCB'
        ],
        'GPBank' => [
            'bankId' => 2032,
            'bankName' => 'GPB'
        ],
        'Saigonbank' => [
            'bankId' => 2033,
            'bankName' => 'SGB'
        ],
        'PGBank' => [
            'bankId' => 2034,
            'bankName' => 'PGB'
        ],
        'Oceanbank' => [
            'bankId' => 2035,
            'bankName' => 'OJB'
        ],
        'NamABank' => [
            'bankId' => 2036,
            'bankName' => 'NAB'
        ],
        'TPB' => [
            'bankId' => 2037,
            'bankName' => 'TPB'
        ],
        'HDB' => [
            'bankId' => 2038,
            'bankName' => 'HDB'
        ],
        'VAB' => [
            'bankId' => 2039,
            'bankName' => 'VAB'
        ],
        'Sacombank' => [
            'bankId' => 2020,
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Prince_rechargeOrder_signstr', [$sign]);
        return strtoupper(md5($sign));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'uid' => $this->rechargeMerchantID,
            'orderid' => substr($order_no,-20),
            'channel' => $this->rechargeTypeList[$this->rechargeType],
            'notify_url' => $this->recharge_callback_url,
            'return_url' => env('SHARE_URL',''),
            'amount' => $money,
            'userip' => getIp(),
            'timestamp' => time(),
            'custom' => $order_no,
        ];
        $params['sign'] = $this->generateSign($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('Prince_rechargeOrder', [$params]);

        $res = $this->requestService->postFormData(self::$url, $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Prince_rechargeOrder_return', [$res]);
        if ($res['status'] != 10000) {
            \Illuminate\Support\Facades\Log::channel('mytest')->info('Prince_rechargeOrder_return', [$res]);
            $this->_msg = "Recharge request failed, status code {$res['status']}";
            return false;
        }
        $native_url = $res['result']['payurl'];

        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['result']['transactionid'],
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Prince_rechargeCallback',$request->post());

        if ($request->status != 10000)  {
            $this->_msg = 'Prince-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
//        $params['result'] = json_encode($params['result'],JSON_UNESCAPED_UNICODE);
        if ($this->generateSign($params,1) <> $sign) {
            $this->_msg = 'Prince-签名错误';
            return false;
        }
        $params['result'] = json_decode($params['result'],true);
        $this->amount = $params['result']['amount'];
        $where = [
            'order_no' => $params['result']['custom'],
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
        $bank = $this->banks[$withdrawalRecord->bank_name] ?? '';
        if(!$bank)
        {
            $this->_msg = '该银行卡不支持提现,请换一张银行卡';
            return false;
        }
        $params = [
            'uid' => $this->withdrawMerchantID,
            'orderid' => $order_no,
            'channel' => 712,
            'notify_url' => $this->withdrawal_callback_url,
            'amount' => intval($money),
            'userip' => "",
            'timestamp' => time(),
            'custom' => "",
            'bank_account' => $withdrawalRecord->account_holder,
            'bank_no' => $withdrawalRecord->bank_number,
            'bank_id' => $bank['bankId'],
            "bank_province" => "no",
            "bank_city" => "no",
            "bank_sub" => "no",
        ];
        $params['sign'] = $this->generateSign($params,2);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Princepay_withdrawalOrder',$params);
        $res = $this->requestService->postFormData(self::$url_cashout, $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Princepay_withdrawalOrder',[$res]);
        if ($res['status'] != 10000) {
            $this->_msg = "大夫请求失败,状态码{$res['status']}";
            return false;
        }
        return  [
            'pltf_order_no' => $res['result']['transactionid'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Princepay_withdrawalCallback',$request->post());

        $pay_status = 0;
        $status = (string)($request->status);
        if($status == 'SUCCESS'){
            $pay_status= 1;
        }
        if($status == 'FAIL'){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'Princepay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'Princepay-签名错误';
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
