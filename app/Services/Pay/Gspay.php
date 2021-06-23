<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Gspay extends PayStrategy
{

    protected static $url = 'https://api.gspayment.in/';    // 支付网关

    protected static $url_cashout = 'https://api.gspayment.in/'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'gspay';   // 支付公司名

    public $withdrawRtn = "success";
    public $rechargeRtn = "success";

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

    public function generateSign(array $params, $type=1){
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $secretKey;
        return strtolower(md5($sign));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'merchant_id' => $this->rechargeMerchantID,
            'pay_type' => 'paytm',
            'trade_amount' => intval($money),
            'notify_url' => $this->recharge_callback_url,
            'redirect_url' => env('SHARE_URL',''),
            'order_sn' => $order_no,
            'order_desc' => 'recharge balance',
            'name' => 'Tommy',
            'mobile' => '13122336688',
            'email' => '11111111@email.com',
        ];
        if(in_array($this->rechargeType, [1,5])){
            $params['bankCode'] = 'BIDV';
        }
        $params['sign'] = $this->generateSign($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('GsPay_rechargeOrder', [$params]);
        $res = $this->requestService->postJsonData(self::$url . 'api/payment/create' , $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('GsPay_rechargeOrder_return', [$res]);
        if ($res['code'] != 1000) {
            $this->_msg = $res['msg'];
            return false;
        }
        $native_url = $res['data']['native_url'];

        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['data']['pltf_order_sn'],
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('GsPay_rechargeCallback',$request->post());

        if ($request->pay_result != 'success')  {
            $this->_msg = 'GsPay-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,1) <> $sign) {
            $this->_msg = 'GsPay-签名错误';
            return false;
        }
        $this->amount = $params['trade_amount'];
        $where = [
            'order_no' => $request->order_sn,
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
            'pay_type' => 'bank',
            'merchant_id' => $this->withdrawMerchantID,
            'order_sn' => $order_no,
            'transfer_amount' => intval($money),
            'upi' => 'xxxx',
            'account_holder' => $withdrawalRecord->account_holder,
            'bank_number' => $withdrawalRecord->bank_number,
            'bank_name' => $withdrawalRecord->bank_name,
            'ifsc_code' => $withdrawalRecord->ifsc_code,
            'notify_url' => $this->withdrawal_callback_url,
            'order_desc' => 'customer withdraw',
            'name' => $withdrawalRecord->account_holder,
            'mobile' => $withdrawalRecord->phone,
            'email' => $withdrawalRecord->email,
        ];
        $params['sign'] = $this->generateSign($params,2);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('GsPay_withdrawalOrder',$params);
        $res = $this->requestService->postJsonData(self::$url_cashout . 'api/withdrawal/create', $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('GsPay_withdrawalOrder',[$res]);
        if ($res['code'] != 1000) {
            $this->_msg = $res['msg'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['data']['pltf_order_sn'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('GsPay_withdrawalCallback',$request->post());

        $pay_status = 0;
        $status = (string)($request->pay_status);
        if($status == 'success'){
            $pay_status= 1;
        }
        if($status == 'fail'){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'GsPay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'GsPay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->order_sn,
            'plat_order_id' => $request->pltf_order_sn,
            'pay_status' => $pay_status
        ];
        return $where;
    }

}
