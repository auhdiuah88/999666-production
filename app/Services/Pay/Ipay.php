<?php


namespace App\Services\Pay;

use App\Repositories\Api\UserRepository;
use App\Services\RequestService;
use Illuminate\Http\Request;

/**
 *  如：unicasino.in  的充值和提现类
 */
class Ipay extends PayStrategy
{

    protected static $url = 'http://ipay-in.yynn.me';

    // 测试环境
//    protected static $merchantID = 10120;
//    protected static $secretkey = 'j3phc11lg986dx3tkai120ngpxy7a2sw';

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    //public static $company = 'ipay';   // 支付公司名

    public $merchantID;
    public $secretkey;
    public $company = 'ipay';   // 支付公司名

    public function _initialize()
    {
//        self::$merchantID = config('pay.company.'.$this->company.'.merchant_id');
//        self::$secretkey = config('pay.company.'.$this->company.'.secret_key');
//        if (empty(self::$merchantID) || empty(self::$secretkey)) {
//            die('请设置 ipay 支付商户号和密钥');
//        }
        $this->merchantID = config('pay.company.'.$this->company.'.merchant_id');
        $this->secretkey = config('pay.company.'.$this->company.'.secret_key');


        $this->recharge_callback_url = self::$url_callback . '/api/recharge_callback' . '?type='.$this->company;
        $this->withdrawal_callback_url =  self::$url_callback . '/api/withdrawal_callback' . '?type='.$this->company;
    }

    /**
     * 生成签名   sign = Md5(key1=vaIue1&key2=vaIue2…商户密钥);
     */
    public function generateSign(array $params)
    {
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . $this->secretkey;
        return md5($sign);
    }

    /**
     * 充值下单接口
     */
    function rechargeOrder($pay_type,$money)
    {
        $order_no = self::onlyosn();
//        $pay_type = 'qrcode';
        $params = [
            'api_name' => 'quickpay.all.native',
            'money' => $money,
            'notify_url' => $this->recharge_callback_url ,
            'order_des' => '支付充值',
            'out_trade_no' => $order_no,
            'shop_id' =>$this->merchantID,
        ];
        $params['sign'] = $this->generateSign($params);
//        print_r($params);die;

        \Illuminate\Support\Facades\Log::channel('mytest')->info('ipay_rechargeOrder', $params);

        $res = $this->requestService->postJsonData(self::$url . '/pay', $params);
        if ($res['rtn_code'] <> 1000) {
            \Illuminate\Support\Facades\Log::channel('mytest')->info('ipay_rechargeOrder', $params);
            $this->_msg = $res['rtn_msg'];
//            $this->_data = $res;
            return false;
        }
        $resData = [
            'out_trade_no' => $order_no,
            'shop_id' => $this->merchantID,
            'pay_company' => $this->company,
            'pay_type' => $pay_type,
            'native_url' => $res['native_url'],
            'pltf_order_id' => $res['pltf_order_id'],
            'verify_money' => $res['verify_money'],
            'match_code' => $res['match_code'],
            'notify_url' => $this->recharge_callback_url ,
        ];
        return $resData;
    }

    function withdrawalOrder(object $withdrawalRecord)
    {
        $account_holder = $withdrawalRecord->account_holder;
        $bank_name = $withdrawalRecord->bank_name;
        $bank_number = $withdrawalRecord->bank_number;
        $ifsc_code = $withdrawalRecord->ifsc_code;
        $upi_id = 'xxxx';
        $money = $withdrawalRecord->payment;    // 打款金额;

//        $order_no = $this->onlyosn();
        $order_no = $withdrawalRecord->order_no;
        $params = [
            'account_holder' => $account_holder, // 银行账户人实名。2、银行卡方式收款，该字段填写真实信息。upi_id字段填"xxxx"。
            'bank_name' => $bank_name, // 银行名称。2、银行卡方式收款，该字段填写真实信息。upi_id字段填"xxxx"。
            'bank_number' => $bank_number, // 银行卡号。2、银行卡方式收款，该字段填写真实信息。upi_id字段填"xxxx"。
            'ifsc_code' => $ifsc_code, // IFSC编号。2、银行卡方式收款，该字段填写真实信息。upi_id字段填"xxxx"。
            'money' => $money,
            'notify_url' => $this->withdrawal_callback_url , // 回调url，用来接收订单支付结果
            'out_trade_no' => $order_no,
            'shop_id' => $this->merchantID,
            'upi_id' => $upi_id, // UPI帐号。1、UPI方式收款，该字段填写真实信息。account_holder、bank_number、bank_name、ifsc_code 这四个字段填"xxxx"。
        ];
        $params['sign'] = $this->generateSign($params);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('ipay_withdrawalOrder',$params);

        $res = $this->requestService->postJsonData(self::$url . '/withdrawal', $params);
        if ($res['rtn_code'] <> 1000) {
            $this->_msg = $res['rtn_msg'];
            return false;
        }
        return [
            'pltf_order_no' => $res['pltf_order_no'],
            'order_no' => $order_no,
            'notify_url' => $this->withdrawal_callback_url,
        ];
    }

    function rechargeCallback(Request $request)
    {
        // 验证参数
        if ($request->shop_id <> $this->merchantID
            || $request->api_name <> 'quickpay.all.native.callback'
            || $request->pay_result <> 'success'
        ) {
            $this->_msg = '参数错误';
            return false;
        }

        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if ($this->generateSign($params) <> $sign){
            $this->_msg = '签名错误';
            return false;
        }

        $where = [
            'order_no' => $request->out_trade_no,
            'pltf_order_id' => $request->pltf_order_id,
//            'money' => $money
        ];

        return $where;
    }

    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('Ipay_withdrawalCallback',$request->post());
        /**
         * {
         * "money": "54.36",
         * "out_trade_no": "202011281743443450333436",
         * "pltf_order_id": "2559202011281743444014",
         * "rtn_code": "success",
         * "sign": "2463f17f8400c0416d0dd86c28208508"
         * }
         */
        if ($request->rtn_code <> 'success') {
            $this->_msg = '参数错误';
            return false;
        }

        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if ($this->generateSign($params) <> $sign) {
            $this->_msg = '签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->out_trade_no,
//            'pltf_order_no' => $request->pltf_order_id,
        ];
        return $where;
    }
}

