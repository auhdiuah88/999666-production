<?php


namespace App\Services\Pay\BR;


use App\Services\Pay\PayStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JBBack extends PayStrategy
{

    protected static $url = 'https://api.jbbank.com.br/1.0/payin/order/create';    // 支付网关

    protected static $url_cashout = 'https://api.jbbank.com.br/1.0/payout/order/create'; // 提现网关

    private $recharge_callback_url = '';     // 充值回调地址
    private $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;

    public $rechargeRtn = "";
    public $withdrawRtn = '';

    public $company = 'jbback';   // 支付公司名

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

        $this->rechargeRtn = json_encode(['code'=>200, 'msg'=>'ok']);
        $this->withdrawRtn = json_encode(['code'=>200, 'msg'=>'ok']);
    }

    public function generateSign($params, $type=1)
    {
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $val) {
            if($val != "")
                $string[] = $key . '=' . $val;
        }
        $sign = implode("&",$string ) . "&key=" . $secretKey;
        return strtolower(md5($sign));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'merchantId' => $this->rechargeMerchantID,
            'merchantOrderId' => $order_no,
            'currency' => 'BRL',
            'amount' => intval($money * 100),
            'merchantNotifyUrl' => $this->recharge_callback_url,
            'merchantReturnUrl' => env('APP_URL',''),
            'payerPhoneArea' => '+55',
            'payerIP' => request()->ip(),
        ];
        $params['sign'] = $this->generateSign($params);
        $header[] = "Content-Type: application/x-www-form-urlencoded";
        \Illuminate\Support\Facades\Log::channel('mytest')->info('jbback-rechargeOrder', [$params]);
        $res = dopost(self::$url, http_build_query($params), $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('jbback-rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = 'Recharge request failed';
            return false;
        }
        if ($res['code'] !== 0) {
            $this->_msg = $res['message'];
            return false;
        }

        $native_url = $res['data']['link'];
        $resData = [
            'pay_type' => $pay_type,
            'out_trade_no' => $order_no,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['data']['orderId'],
            'verify_money' => '',
            'match_code' => '',
            'params' => $params,
            'is_post' => isset($is_post) ? $is_post : 0
        ];
        return $resData;
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('jbback-rechargeCallback', $request->post());
        $params = $request->post();
        if ($params['status'] != 'PAID')  {
            $this->_msg = 'jbbank-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        unset($params['type']);
        unset($params['sign']);
        if ($this->generateSign($params) <> $sign) {
            $this->_msg = 'jbback-签名错误';
            return false;
        }
        $this->amount = $params['amount'] / 100;
        $where = [
            'order_no' => $params['merchantOrderId'],
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
            'merchantId' => $this->withdrawMerchantID,
            'merchantOrderId' => $order_no,
            'currency' => 'BRL',
            'amount' => intval($money * 100),
            'merchantNotifyUrl' => $this->withdrawal_callback_url,
            'type' => 1,
            'receiverName' => $withdrawalRecord->account_holder,
            'receiverIdCard' => $withdrawalRecord->ifsc_code,  //cpf
            'receiverPhoneArea' => '+55',
            'receiverPhoneNum' => $withdrawalRecord->phone,
            'receiverEmail' => $withdrawalRecord->mail,
            'receiverPixType' => 'CPF',
            'receiverPixAccount' => $withdrawalRecord->bank_num,  //pix
        ];
        $params['sign'] = $this->generateSign($params,2);
        $header[] = "Content-Type: application/x-www-form-urlencoded";
        \Illuminate\Support\Facades\Log::channel('mytest')->info('jbback-withdraw_params', $params);
        $res = dopost(self::$url_cashout, http_build_query($params), $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('jbback-withdraw_return', [$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['code'] !== 0) {
            $this->_msg = $res['message'];
            return false;
        }
        return [
            'pltf_order_no' => $res['data']['orderId'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('yipay-withdrawalCallback', $request->post());
        $params = $request->post();
        $pay_status = 0;
        $status = $params['status'];
        if($status === 'TRANSFERRED'){
            $pay_status= 1;
        }
        if($status === 'FAILED' || $status === 'CANCELED'){
            $pay_status = 2;
        }
        if ($pay_status == 0) {
            $this->_msg = 'jbbank-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'yipay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $params['merchantOrderId'],
            'plat_order_id' => $params['orderId'],
            'pay_status' => $pay_status
        ];
        return $where;
    }
}
