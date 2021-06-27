<?php


namespace App\Services\Pay\BR;


use App\Services\Pay\PayStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Conduit extends PayStrategy
{

    protected static $url = 'https://api20.cyzxw.com.cn/wallet-service/open/payment/createOrder';    // 支付网关

    protected static $url_cashout = 'http://bra.polymerizations.com/'; // 提现网关

    private $recharge_callback_url = '';     // 充值回调地址
    private $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;

    public $rechargeRtn = 'success';
    public $withdrawRtn = 'success';

    public $company = 'BRHX';   // 支付公司名

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

    public function generateSIgn($params, $flag = 1)
    {
        $secret = $flag == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' . $secret;
        return strtoupper(md5($sign));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'mchId' => $this->rechargeMerchantID,
            'mchOrderNo' => $order_no,
            'currency' => 'USDT',
            'amount' => intval($money),
            'clientIp' => '',

            'mer_no' => $this->rechargeMerchantID,
            'mer_order_no' => $order_no,
            'pname' => 'Por favor digite seu nome',
            'pemail' => '88888888@mail',
            'phone' => '8888888888',
            'order_amount' => (string)intval($money),
            'country_code' => 'BRA',
            'cyy_no' => 'BRL',
            'pay_type' => 'PIX',
            'notify_url' => $this->recharge_callback_url,
            'callback_url' => env('APP_URL', ''),
        ];
        $params['sign'] = $this->generateSIgn($order_no);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('BRHX_rechargeParams', [$params]);
        $res = dopost(self::$url . 'poi/pay/index/PayOrderCreate', $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('BRHX_return', [$res]);
        $res = json_decode($res, true);
        if (!$res || $res['code'] != 1) {
            $this->_msg = "prepay failed";
            return false;
        }
        $native_url = $res['pay_url'];
        $resData = [
            'pay_type' => $pay_type,
            'out_trade_no' => $order_no,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['order_number'],
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('BRHX_rechargeCallback', $request->post());
        $params = $request->post();
        if ($params['code'] != 1 || $params['order_status'] != 4) {
            $this->_msg = 'BRHX-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        if ($this->generateSIgn($params['mer_order_no']) <> $sign) {
            $this->_msg = 'BRHX-签名错误';
            return false;
        }
        $this->amount = $params['pay_amount'];
        $where = [
            'order_no' => $params['mer_order_no'],
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
        $bank_id = DB::table('banks')->where("bank_name", $withdrawalRecord->bank_name)->value('banks_id');
        if (!$bank_id) {
            $this->_msg = '用户提现银行错误';
            return false;
        }
        if (!isset($this->banks[$bank_id])) {
            $this->_msg = '用户提现银行错误';
            return false;
        }
        $bank_code = $this->banks[$bank_id]['bankCode'];
        $params = [
            'mer_no' => $this->withdrawMerchantID,
            'mer_order_no' => $order_no,
            'order_amount' => intval($money),
            'pay_type' => 'PIX',
            'cyy_no' => 'BRL',
            'acc_no' => 'BRL',
            'acc_name' => $withdrawalRecord->account_holder,
            'cpf' => $withdrawalRecord->bank_number,
            'bank_code' => $withdrawalRecord->ifsc_code,
            'bank_encrypt' => $bank_code,
            'notifyurl' => $this->withdrawal_callback_url
        ];
        $params['sign'] = $this->generateSIgn($order_no, 2);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('BRHX_withdraw_params', [$params]);
        $res = dopost(self::$url_cashout . 'poi/dai/index/DaiOrderCreate', $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('BRHX_withdraw_return', [$res]);
        $res = json_decode($res, true);
        if (!$res) {
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['code'] != 1) {
            $this->_msg = $res['msg'];
            return false;
        }
        return [
            'pltf_order_no' => $res['order_number'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('BRHX_withdrawalCallback', $request->post());
        $params = $request->post();
        if ($params['code'] != 1) {
            $this->_msg = 'BRHX-withdrawal-交易未完成';
            return false;
        }
        $pay_status = 0;
        $status = (int)$params['order_status'];
        if ($status == 4) {
            $pay_status = 1;
        }
        if ($status == -1 || $status == 3) {
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'BRHX-withdrawal-交易未完成';
            return false;
        }
        // 验证签名

        $sign = $params['sign'];
        if ($this->generateSIgn($params['mer_order_no'], 2) <> $sign) {
            $this->_msg = 'BRHX-签名错误';
            return false;
        }
        $where = [
            'order_no' => $params['mer_order_no'],
            'plat_order_id' => $params['order_no'],
            'pay_status' => $pay_status
        ];
        return $where;
    }
}
