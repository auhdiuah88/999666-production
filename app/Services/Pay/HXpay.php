<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HXpay extends PayStrategy
{

    protected static $url = 'https://upi.cash/';    // 支付网关

    protected static $url_cashout = 'https://upi.cash/'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;

    public $rechargeRtn = 'SUCCESS';
    public $withdrawRtn = 'SUCCESS';

    protected $RECHARGE_MERCHANT_ACCESS_KEY = '';
    protected $WITHDRAW_MERCHANT_SECRET_KEY = '';

    public $company = 'HXpay';   // 支付公司名

    public function _initialize()
    {
        $withdrawConfig = DB::table('settings')->where('setting_key','withdraw')->value('setting_value');
        $rechargeConfig = DB::table('settings')->where('setting_key','recharge')->value('setting_value');
        $withdrawConfig && $withdrawConfig = json_decode($withdrawConfig,true);
        $rechargeConfig && $rechargeConfig = json_decode($rechargeConfig,true);

        $this->withdrawMerchantID = isset($withdrawConfig[$this->company])?$withdrawConfig[$this->company]['merchant_id']:"";
        $this->withdrawSecretkey = isset($withdrawConfig[$this->company])?$withdrawConfig[$this->company]['secret_key']:"";
        $this->WITHDRAW_MERCHANT_SECRET_KEY = $withdrawConfig['public_key'];

        $this->rechargeMerchantID = isset($rechargeConfig[$this->company])?$rechargeConfig[$this->company]['merchant_id']:"";
        $this->rechargeSecretkey = isset($rechargeConfig[$this->company])?$rechargeConfig[$this->company]['secret_key']:"";
        $this->RECHARGE_MERCHANT_ACCESS_KEY = $rechargeConfig['public_key'];

        $this->recharge_callback_url = self::$url_callback . '/api/recharge_callback' . '?type='.$this->company;
        $this->withdrawal_callback_url =  self::$url_callback . '/api/withdrawal_callback' . '?type='.$this->company;
    }

    public function rechargeGenerateSign($order_no, $amount)
    {
        $string = $this->rechargeSecretkey . $order_no . $amount . $this->RECHARGE_MERCHANT_ACCESS_KEY;
        return md5($string);
    }

    public function rechargeBackGenerateSign($data)
    {
        $string = $this->rechargeSecretkey . $data['platform_osn'] . $data['status'] . $data['amount'] . $this->RECHARGE_MERCHANT_ACCESS_KEY;
        return md5($string);
    }

    public function withdrawGenerateSign($order_sn, $account, $upi_handle, $amount)
    {
        $string = $this->withdrawSecretkey . $order_sn . $account . $upi_handle . $amount . $this->WITHDRAW_MERCHANT_SECRET_KEY;
        return md5($string);
    }

    public function withdrawBackGenerateSign($data)
    {
        $string = $this->withdrawSecretkey . $data['platform_osn'] . $data['status'] . $data['amount'] . $this->WITHDRAW_MERCHANT_SECRET_KEY;
        return md5($string);
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'merchant_sn' => $this->rechargeMerchantID,
            'order_sn' => $order_no,
            'amount' => intval($money),
            'name' => 'skyshop',
            'email' => "skyshop@email.com",
            'phone' => "98989898",
            'remark' => 'customer recharge',
            'redirect_url' => env('APP_URL',''),
        ];
        $params['sign'] = $this->rechargeGenerateSign($order_no,intval($money));
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('HXpay_rechargeParams',[$params]);
        $res =dopost(self::$url . 'gateway/payin/', $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('HXpay_return',[$res]);
        $res = json_decode($res,true);
        if (!$res || $res['code'] != 0) {
            $this->_msg = "prepay failed";
            return false;
        }
        $native_url = $res['data']['pay_url'];
        $resData = [
            'out_trade_no' => $order_no,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['data']['platform_osn'],
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('HXpay_rechargeCallback',$request->post());
        $params = $request->post();
        if ((int)$params['status'] != 1)  {
            $this->_msg = 'HX-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        if ($this->rechargeBackGenerateSign($params) <> $sign) {
            $this->_msg = 'HX-签名错误';
            return false;
        }
        $this->amount = $params['amount'];
        $where = [
            'order_no' => $params['order_sn'],
        ];
        return $where;
    }

    /**
     *  后台审核请求提现订单 (提款)  代付方式
     */
    public function withdrawalOrder(object $withdrawalRecord)
    {
        $money = $withdrawalRecord['real_num'];    // 打款金额
        $order_no = $withdrawalRecord['out_trade_no'];
        $params = [
            'merchant_sn' => $this->withdrawMerchantID,
            'order_sn' => $order_no,
            'amount' => intval($money),
            'type' => 'bank',
            'name' => $withdrawalRecord->account_holder,
            'account' => $withdrawalRecord->bank_number,
            'ifsc' => $withdrawalRecord->ifsc_code,
            'remark' => 'customer withdraw',
        ];
        $params['sign'] = $this->withdrawGenerateSign($order_no,$withdrawalRecord->bank_number,'',intval($money));
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('HXpay_withdraw_params',[$params]);
        $res =dopost(self::$url_cashout . 'gateway/payout/', $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('HX_withdraw_return',[$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['code'] != 0) {
            $this->_msg = $res['message'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['data']['platform_osn'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('HXpay_withdrawalCallback',$request->post());
        $params = $request->post();
        $pay_status = 0;
        $status = (int)$params['status'];
        if($status == 1){
            $pay_status= 1;
        }
        if($status == 9){
            $pay_status = 2;
        }
        if ($pay_status == 0) {
            $this->_msg = 'HX-withdrawal-交易未完成';
            return false;
        }
        // 验证签名

        $sign = $params['sign'];
        if ($this->withdrawBackGenerateSign($params) <> $sign) {
            $this->_msg = 'HX-签名错误';
            return false;
        }
        $where = [
            'order_no' => $params['order_sn'],
            'plat_order_id' => $params['platform_osn'],
            'pay_status' => $pay_status
        ];
        return $where;
    }

}
