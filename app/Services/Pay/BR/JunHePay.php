<?php


namespace App\Services\Pay\BR;


use App\Services\Pay\PayStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JunHePay extends PayStrategy
{

    protected static $url = 'https://bra.junhepay.com/web/index.html';    // 支付网关

    protected static $url_cashout = 'https://bra.junhepay.com/api/otcPayOrder/unifiedOrder'; // 提现网关

    protected static $login = 'https://bra.junhepay.com/api/otcAppUser/login';  //签名登录url

    private $recharge_callback_url = '';     // 充值回调地址
    private $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;

    public $rechargeRtn = "success";
    public $withdrawRtn = 'success';

    public $company = 'junhe';   // 支付公司名

    public function _initialize()
    {
        $withdrawConfig = DB::table('settings')->where('setting_key', 'withdraw')->value('setting_value');
        $rechargeConfig = DB::table('settings')->where('setting_key', 'recharge')->value('setting_value');
        $withdrawConfig && $withdrawConfig = json_decode($withdrawConfig, true);
        $rechargeConfig && $rechargeConfig = json_decode($rechargeConfig, true);

        $this->withdrawMerchantID = isset($withdrawConfig[$this->company]) ? $withdrawConfig[$this->company]['merchant_id'] : "";
        $this->withdrawSecretkey = isset($withdrawConfig[$this->company]) ? $withdrawConfig[$this->company]['secret_key'] : "";

        $this->rechargeMerchantID = isset($rechargeConfig[$this->company]) ? $rechargeConfig[$this->company]['merchant_id'] : "";
        $this->rechargeSecretkey = isset($rechargeConfig[$this->company]) ? $rechargeConfig[$this->company]['secret_key'] : "";

        $this->recharge_callback_url = self::$url_callback . '/api/recharge_callback' . '?type=' . $this->company;
        $this->withdrawal_callback_url = self::$url_callback . '/api/withdrawal_callback' . '?type=' . $this->company;
    }

    protected $banks = [
        131 => [
            'bankCode' => '104001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        132 => [
            'bankCode' => '001001',
            'bankName' => 'Banco do Brasil',
        ],
        133 => [
            'bankCode' => '237001',
            'bankName' => 'Banco Bradesco',
        ],
        134 => [
            'bankCode' => '341001',
            'bankName' => 'Banco Itau',
        ],
        135 => [
            'bankCode' => '033001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        136 => [
            'bankCode' => '121001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        137 => [
            'bankCode' => '318001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        138 => [
            'bankCode' => '218001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        139 => [
            'bankCode' => '070001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        140 => [
            'bankCode' => '745001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        141 => [
            'bankCode' => '756001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        142 => [
            'bankCode' => '748001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        143 => [
            'bankCode' => '003001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        144 => [
            'bankCode' => '707001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        145 => [
            'bankCode' => '087001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        146 => [
            'bankCode' => '047001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        147 => [
            'bankCode' => '037001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        148 => [
            'bankCode' => '041001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        149 => [
            'bankCode' => '004001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        150 => [
            'bankCode' => '399001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        151 => [
            'bankCode' => '653001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        152 => [
            'bankCode' => '077001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        153 => [
            'bankCode' => '389001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        154 => [
            'bankCode' => '260001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        155 => [
            'bankCode' => '212001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        156 => [
            'bankCode' => '633001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        157 => [
            'bankCode' => '422001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        158 => [
            'bankCode' => '655001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        159 => [
            'bankCode' => '021001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        160 => [
            'bankCode' => '755001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        161 => [
            'bankCode' => '085001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        162 => [
            'bankCode' => '090001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        163 => [
            'bankCode' => '136001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        164 => [
            'bankCode' => '133001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        165 => [
            'bankCode' => '254001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
        166 => [
            'bankCode' => '084001',
            'bankName' => 'Banco Caixa Economica Federal',
        ],
    ];

    public function generateSign($params, $flag = 1)
    {
        $secret = $flag == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        $secret = 'safdsrewsdsfdsewffew123';
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if($value != "")
                $string[] = $key . '=' . $value;
        }
        $sign = implode('&', $string);
        $sign = urlencode($sign);
        Log::channel('mytest')->info('JunHe-login-sign-str',[$sign]);
        return hash_hmac('sha1',$sign,$secret);
    }

    protected function signLogin($flag=1)
    {
        $params = [
            'appId' => $flag == 1 ? $this->rechargeMerchantID : $this->withdrawMerchantID,
            'ts' => time() * 1000,
            'terminalType' => 'app'
        ];
        $params = [
            'appId' => 'fdsafdsafdfdsafsafdsrewq',
            'ts' => '1561949184369',
            'terminalType' => 'app'
        ];
        $params['sign'] = $this->generateSign($params, $flag);
        Log::channel('mytest')->info('JunHe-login-sign',$params);
        $res = $this->requestService->postFormData(self::$login, $params);
        Log::channel('mytest')->info('JunHe-login-return',[$res]);
        if($res['code'] != 200)
        {
            $this->_msg = $res['message'];
            return false;
        }
        return $res['data'];
    }

    protected function createNativeUrl($params):string
    {
        $url = self::$url . "?";
        foreach ($params as $key => $val)
        {
            $url .= $key .'='. $val . '&';
        }
        return trim($url,'&');
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        if(!$token = $this->signLogin())
        {
            return false;
        }
        $user = $this->getUser();
        $params = [
            'outOrderNo' => $order_no,
            'token' => $token,
            'amount' => intval($money),
            'payType' => 9,
            'notifyUrl' => $this->recharge_callback_url,
            'userFlag' => $user->phone,
        ];
        $is_post=1;

        $native_url = $this->createNativeUrl($params);
        $resData = [
            'pay_type' => $pay_type,
            'out_trade_no' => $order_no,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => '',
            'verify_money' => '',
            'match_code' => '',
            'is_post' => isset($is_post) ? $is_post : 0
        ];
        return $resData;
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('JunHe_rechargeCallback', $request->post());
        $params = $request->post();
        if($params['orderState'] != 1) {
            $this->_msg = 'JunHe-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params) <> $sign) {
            $this->_msg = 'JunHe-签名错误';
            return false;
        }
        $this->amount = $params['tradeAmount'];
        $where = [
            'order_no' => $params['outOrderNo'],
        ];
        $this->rechargeRtn = json_encode([
            'code' => 200,
            'data' => true,
            'message' => '请求成功',
            'success' => true,
        ]);
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
            'appId' => $this->withdrawMerchantID,
            'terminalType' => 'app',
            'ts' => time() * 1000,
            'payType' => 4,
            'tradeAmount' => intval($money),
            'outOrderNo' => $order_no,
            'notifyUrl' => $this->withdrawal_callback_url,
            'upiAccount' => $withdrawalRecord->bank_number,
            'ifscCode' => '',
            'receiveName' => '',
            'receiveAccount' => '',
            'bankName' => '',
            'customerName' => $withdrawalRecord->account_holder,
        ];
        $params['sign'] = $this->generateSign($order_no, 2);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('JunHe_withdraw_params', $params);
        $res = $this->requestService->postFormData(self::$url_cashout, $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('JunHe_withdraw_return', [$res]);
        if (!$res) {
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['code'] != 200 && $res['success'] !== true) {
            $this->_msg = $res['message'];
            return false;
        }
        return [
            'pltf_order_no' => '',
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('JunHe_withdrawalCallback', $request->post());
        $params = $request->post();
//        if ($params['code'] != 1) {
//            $this->_msg = 'BRHX-withdrawal-交易未完成';
//            return false;
//        }
        $pay_status = 0;
        $status = (int)$params['orderState'];
        if ($status == 1) {
            $pay_status = 1;
        }
        if ($status == 2) {
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'JunHe-withdrawal-交易未完成';
            return false;
        }
        // 验证签名

        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSIgn($params, 2) <> $sign) {
            $this->_msg = 'JunHe-签名错误';
            return false;
        }
        $where = [
            'order_no' => $params['outOrderNo'],
            'plat_order_id' => '',
            'pay_status' => $pay_status
        ];
        return $where;
    }
}
