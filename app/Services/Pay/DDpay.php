<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DDpay extends PayStrategy
{

    protected static $url = 'https://api.paydds.com/order';    // 支付网关

    protected static $url_cashout = 'https://api.paydds.com/payment'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'ddpay';   // 支付公司名
    public $rechargeRtn = 'success';
    public $withdrawRtn = 'success';

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
    public  function generateSign(array $params, $type=1)
    {
        if(!isset($params['merchantId']))$params['merchantId'] = $params['MerchantId'];
        if(!isset($params['merchantOrderNumber']))$params['merchantOrderNumber'] = $params['MerchantOrderNumber'];
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        return strtolower(md5($params['merchantId'].$params['merchantOrderNumber'].$secretKey));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'merchantId' => $this->rechargeMerchantID,
            'orderPrice' => intval($money),
            'merchantOrderNumber' => $order_no,
            'notifyUrl' => $this->recharge_callback_url,
        ];
        $params['sign'] = $this->generateSign($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('DDPay_rechargeOrder', [$params]);
//        $params_string = json_encode($params);
        $header[] = "Content-Type: application/x-www-form-urlencoded";
//        $header[] = "Content-Length: " . strlen($params_string);
        $res =dopost(self::$url, $params, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('DDPay_rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "prepay failed";
            return false;
        }
        if ($res['code'] != 0) {
            $this->_msg = $res['msg'];
            return false;
        }
        $native_url = $res['data'];

        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => '',
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('DDPay_rechargeCallback',$request->input());

        if ($request->MerchantDepositStatus != 4)  {
            $this->_msg = 'DDPay-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,1) <> $sign) {
            $this->_msg = 'DDPay-签名错误';
            return false;
        }
        $this->amount = $params['RealPrice'];
        $where = [
            'order_no' => $request->MerchantOrderNumber,
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
            'MerchantOrderNumber' => $order_no,
            'Name' => $withdrawalRecord->account_holder,
            'BankAccount' => $withdrawalRecord->bank_number,
            'BankName' => $withdrawalRecord->bank_name,
            'Amount' => intval($money),
            'CallbackUrl' => $this->withdrawal_callback_url,
            'Email' => $withdrawalRecord->email,
            'Mobile' => $withdrawalRecord->phone,
            'IFSC' => $withdrawalRecord->ifsc_code,
            'MerchantId' => $this->withdrawMerchantID,
        ];
        $params['sign'] = $this->generateSign($params,2);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('DDPay_withdrawalOrder',$params);
        $res =dopost(self::$url_cashout, $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('DDPay_withdrawalOrder',[$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['code'] != 0) {
            $this->_msg = $res['msg'];
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('DDPay_withdrawalCallback',$request->input());

        $pay_status = 0;
        $status = (int)($request->MerchantPaymentStatus);
        if($status == 4){
            $pay_status= 1;
        }
        if($status == 2){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'DDPay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'DDPay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->MerchantOrderNumber,
            'plat_order_id' => $request->Id,
            'pay_status' => $pay_status
        ];
        return $where;
    }

}
