<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TongLinkPay extends PayStrategy
{

    protected static $url = 'https://tokushimapay.com/api/pay';    // 支付网关

    protected static $url_cashout = 'https://tokushimapay.com/api/autoWithdrawal'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;

    public $rechargeRtn = 'SUCCESS';
    public $withdrawRtn = 'SUCCESS';

    public $company = 'TongLink';   // 支付公司名

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

    public function generateSign($appid, $paytype, $orderamount, $orderno)
    {
        $secret = $this->rechargeSecretkey;
        $string = $appid . $paytype . $orderamount . $orderno . $secret;
        return md5(strtoupper($string));
    }

    public function generateRechargeCallbackSign($appid,  $orderno, $actualamount, $status)
    {
        $secret = $this->rechargeSecretkey;
        $string = $appid . $orderno . $actualamount . $status . $secret;
        return md5(strtoupper($string));
    }

    public function generateWithdrawSign($params){
        ksort($params);
        $params['privateKey'] = $this->withdrawSecretkey;
        $string = [];
        foreach ($params as $key => $value) {
            if($value != '')
                $string[] = $key . '=' . $value;
        }
        $sign = implode('&', $string);
        return md5(strtoupper($sign));
    }

    public function generateWithdrawCallbackSign($appid,  $orderno, $actualamount, $status)
    {
        $secret = $this->withdrawSecretkey;
        $string = $appid . $orderno . $actualamount . $status . $secret;
        return md5(strtoupper($string));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'appid' => $this->rechargeMerchantID,
            'paytype' => 'PIX',
            'orderamount' => intval($money),
            'orderno' => $order_no,
            'notifyurl' => $this->recharge_callback_url,
            'returnurl' => env('APP_URL',''),
        ];
        $params['sign'] = $this->generateSign($params['appid'], $params['paytype'], $params['orderamount'], $params['orderno']);
//        $params_string = json_encode($params);
//        $header[] = "Content-Type: application/json";
//        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('TongLink_rechargeParams',[$params]);
//        $res =dopost(self::$url, $params_string, $header);
//        \Illuminate\Support\Facades\Log::channel('mytest')->info('TongLink_rechargeReturn',[$res]);
//        $res = json_decode($res,true);
//        if (!$res || $res['code'] != 1) {
//            $this->_msg = "prepay failed";
//            return false;
//        }
        $native_url = self::$url;
        $resData = [
            'pay_type' => $pay_type,
            'out_trade_no' => $order_no,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => '',
            'verify_money' => '',
            'match_code' => '',
            'params' => $params,
            'is_post' => 2
        ];
        return $resData;
    }

    public function getUrlParam($a){
        $res = array();
        foreach ($a as $k => $v) {
            $arr = explode('=', $v);
            $res[$arr[0]] = $arr[1];
        }
        return $res;
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
//        \Illuminate\Support\Facades\Log::channel('mytest')->info('TongLink_rechargeCallback',$request->input());
        \Illuminate\Support\Facades\Log::channel('mytest')->info('TongLink_rechargeCallback',[$request->getQueryString()]);
        $params = $request->getQueryString();
        $params = urldecode($params);
        $arr = explode("?", $params);
        $a = $this->getUrlParam(explode("&", $arr[0]));
        $b = $this->getUrlParam(explode("&", $arr[1]));
        $params = array_merge($a,$b);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('TongLink_rechargeCallback2',[$params]);
        if ($params['status'] != 'SUCCESS')  {
            $this->_msg = 'TongLink-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        if ($this->generateRechargeCallbackSign($params['appid'], $params['orderno'], $params['actualamount'], $params['status']) <> $sign) {
            $this->_msg = 'TongLink-签名错误';
            return false;
        }
        $this->amount = $params['actualamount'];
        $where = [
            'order_no' => $params['orderno'],
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
            'appid' => $this->withdrawMerchantID,
            'settAmount' => intval($money),
            'orderno' => $order_no,
            'notifyurl' => $this->withdrawal_callback_url,
            'payType' => 'PIX',
            'ifscCode' => $withdrawalRecord->ifsc_code,
            'bankaccountname' => $withdrawalRecord->account_holder,
            'cardno' => $withdrawalRecord->bank_number,
        ];
        $params['sign'] = $this->generateWithdrawSign($params);
        $header[] = "Content-Type: application/x-www-form-urlencoded";
        \Illuminate\Support\Facades\Log::channel('mytest')->info('TongLink_withdraw_params',[$params]);
        $res =dopost(self::$url_cashout, $params, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('TongLink_withdraw_return',[$res]);

        if(!$res || $res != 'SUCCESS'){
            $this->_msg = '提交代付失败';
            return false;
        }
        return  [
            'pltf_order_no' => '',
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
//        \Illuminate\Support\Facades\Log::channel('mytest')->info('TongLink_withdrawalCallback',$request->input());
        \Illuminate\Support\Facades\Log::channel('mytest')->info('TongLink_withdrawalCallback2', [$request->getQueryString()]);
        $params = $request->getQueryString();
        $params = urldecode($params);
        $arr = explode("?", $params);
        $a = $this->getUrlParam(explode("&", $arr[0]));
        $b = $this->getUrlParam(explode("&", $arr[1]));
        $params = array_merge($a,$b);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('TongLink_withdrawalCallback',[$params]);
//        $params = $request->input();
        if(!isset($params['status'])){
            $this->_msg = 'TongLink-withdrawal-交易未完成';
            return false;
        }
        $pay_status = 0;
        $status = (int)$params['status'];
        if($status == 'SUCCESS'){
            $pay_status= 1;
        }
        if($status == 'FAILE'){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'TongLink-withdrawal-交易未完成';
            return false;
        }
        // 验证签名

        $sign = $params['sign'];
        if ($this->generateWithdrawCallbackSign($params['appid'], $params['orderno'], $params['remitamount'], $params['status']) <> $sign) {
            $this->_msg = 'TongLink-签名错误';
            return false;
        }
        $where = [
            'order_no' => $params['orderno'],
            'plat_order_id' => '',
            'pay_status' => $pay_status
        ];
        return $where;
    }

}
