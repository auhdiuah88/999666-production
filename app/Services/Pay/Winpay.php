<?php


namespace App\Services\Pay;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Winpay支付商
 */
class Winpay extends PayStrategy
{
    protected static $url = 'https://www.winpays.in';    // 支付网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    private $compalateUrl = 'page';  // 支付完成返回地址

    //public static $company = 'winpay';   // 支付公司名

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public  $company = 'winpay';   // 支付公司名

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

    /**
     * 生成签名  sign = Md5(key1=vaIue1&key2=vaIue2&key=签名密钥);
     */
    public function generateSign(array $params, $type=1)
    {
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' . $this->secretkey;
//        dump(self::$merchantID);
//        dd(self::$secretkey);
        return strtolower(md5($sign));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $user = $this->getUser();

        $order_no = self::onlyosn();
//        $pay_type = 'QUICK_PAY';
        $params = [
            'merchant' => $this->rechargeMerchantID,
            'orderId' => $order_no,
            'amount' => $money,
            'customName' => $user->nickname,
//            'customMobile' => $user->phone,  // 666666666666
            'customMobile' => '666666666',  // 666666666666
            'customEmail' => '123@gmail.com',
//            'channelType' => $pay_type,   // UPI   QUICK_PAY
            'notifyUrl' => $this->recharge_callback_url,
            'callbackUrl' => $this->compalateUrl,
        ];
        $params['sign'] = $this->generateSign($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('winpay_rechargeOrder', $params);

        $res = $this->requestService->postFormData(self::$url . '/openApi/pay/createOrder', $params);
        if ($res['success'] === false) {
            $this->_msg = $res['errorMessages'];
            return false;
        }
        $resData = [
            'out_trade_no' => $order_no,
            'shop_id' => $this->rechargeMerchantID,
            'pay_company' => $this->company,
            'pay_type' => $pay_type,
            'native_url' => $res['data']['url'],
            'pltf_order_id' => $res['data']['platOrderId'],
            'verify_money' => '',
            'match_code' => '',
            'notify_url' => $this->recharge_callback_url,
        ];
        return $resData;
    }

    /**
     *  后台审核请求提现订单 (提款)  代付方式
     */
    public function withdrawalOrder(object $withdrawalRecord)
    {
        // 1 银行卡 2 Paytm 3代付
        $pay_type = 3;

        $money = $withdrawalRecord->payment;    // 打款金额
        $ip = $this->request->ip();

//        $order_no = self::onlyosn();
        $order_no = $withdrawalRecord->order_no;

        /**
            merchant	是	string	商户号，平台分配账号
            orderId	是	string	商户订单号（唯一），字符长度40以内
            amount	是	number	金额，单位卢币(最多保留两位小数)
            customName	是	string	收款人姓名
            customMobile	是	string	收款人电话
            customEmail	是	string	收款人email地址
            bankCode	否	string	收款人银行代码，见数据字典
            bankAccount	是	string	收款人银行账号
            ifscCode	是	string	收款人IFSC CODE
            notifyUrl	是	string	通知回调地址
            sign	是	string	签名
         */

        $bank_name = $withdrawalRecord->bank_name;
        $account_holder = $withdrawalRecord->account_holder;
        $bank_number = $withdrawalRecord->bank_number;
        $ifsc_code = $withdrawalRecord->ifsc_code;
        $phone = $withdrawalRecord->phone;
        $email = $withdrawalRecord->email;

        $params = [
//            'type' => $pay_type,    // 1 银行卡 2 Paytm 3代付
            'merchant' => $this->withdrawMerchantID,
            'orderId' => $order_no,
            'amount' => $money,
            'customName' => $account_holder,
            'customMobile' => $phone,
            'customEmail' => $email,
//            'bankCode' => time(),
            'bankAccount' =>$bank_number,
            'ifscCode' => $ifsc_code,
            'notifyUrl' => $this->withdrawal_callback_url,
        ];
        $params['sign'] = $this->generateSign($params,2);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('winpay_withdrawalOrder',$params);
        $res = $this->requestService->postFormData(self::$url . '/openApi/payout/createOrder', $params);
        \Illuminate\Support\Facades\Log::channel('kidebug')->info('winpay_withdrawalOrder',$res);
        if ($res['success'] != true) {
            $this->_msg = $res['errorMessages'];
            return false;
        }
        return [
            'pltf_order_no' => $res['platOrderId'],
            'order_no' => $order_no,
            'notify_url' => $this->withdrawal_callback_url,
        ];
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('winpay_rechargeCallback', $request->post());

        if ($request->status != 'PAY_SUCCESS') {
            $this->_msg = 'winpay-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if ($this->generateSign($params,1) <> $sign) {
            $this->_msg = 'winpay-签名错误';
            return false;
        }

        $where = [
            'order_no' => $params['orderId'],
        ];
        return $where;
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('winpay_withdrawalCallback', $request->post());

        if ($request->status != 'PAY_SUCCESS') {
            $this->_msg = 'winpay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'winpay-签名错误';
            return false;
        }
        \Illuminate\Support\Facades\Log::channel('mytest')->info('winpay_withdrawalCallback', $params);
        $where = [
            'order_no' => $params['orderId'],
            'plat_order_id' => $params['platOrderId']
        ];
        return $where;
    }
}

