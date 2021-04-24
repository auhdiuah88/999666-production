<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SevenIndiaPay extends PayStrategy
{

    protected static $url = 'https://api.zf77777.org/';    // 支付网关

    protected static $url_cashout = 'https://api.zf77777.org/'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = '77pay';   // 支付公司名

    public $amountFiled = 'payamount';

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

        $this->recharge_callback_url = self::$url_callback . '/api/recharge_callback' . '?type='.$this->company.'&backup_type=' . $this->company;
        $this->withdrawal_callback_url =  self::$url_callback . '/api/withdrawal_callback' . '?type='.$this->company.'&backup_type=' . $this->company;
    }

    protected $rechargeTypeList = [
        '1' => 'upi',
        '2' => 'sbi',
        '3' => 'icici',
        '4' => 'razorpay',
        '5' => 'instamojo',
    ];

    public function generateSignRigorous(array $params, $type=1){
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        $string = $secretKey . $params['orderid'] . (string)$params['amount'];
        return strtolower(md5($string));
    }

    public function generateSignRigorous2(array $params, $type=1){
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        $string = $secretKey . $params['orderid'] . (string)$params['payamount'];
        return strtolower(md5($string));
    }

    public function generateSignRigorous3(array $params, $type=1){
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        $string = $secretKey . $params['orderid'] . (string)$params['qrurl'];
        return strtolower(md5($string));
    }



    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'userid' => $this->rechargeMerchantID,
            'orderid' => $order_no,
            'type' => $this->rechargeTypeList[$this->rechargeType],
            'amount' => intval($money),
            'notifyurl' => $this->recharge_callback_url,
            'returnurl' => env('SHARE_URL',''),
            'note' => 'recharge balance'
        ];
        $params['sign'] = $this->generateSignRigorous($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('seven_india_rechargeOrder', [$params]);

        $res = $this->requestService->postJsonData(self::$url . 'api/create' , $params);
        if ($res['success'] != 1) {
            \Illuminate\Support\Facades\Log::channel('mytest')->info('seven_india_rechargeOrder_return', $res);
            $this->_msg = $res['message'];
            return false;
        }
        $native_url = $res['pageurl'];
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['ticket'],
            'verify_money' => '',
            'match_code' => '',
            'is_post' => $is_post ?? 0,
        ];
        return $resData;
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('seven_india_rechargeCallback',$request->input());
        if (isset($request->qrurl)){
            $params = $request->input();
            $sign = $params['sign'];
            if ($this->generateSignRigorous3($params,1) <> $sign) {
                $this->_msg = 'seven_india-签名错误';
                return false;
            }
            return true;
        }
        if ($request->ispay != 1)  {
            $this->_msg = 'seven_india-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSignRigorous2($params,1) <> $sign) {
            $this->_msg = 'seven_india-签名错误';
            return false;
        }

        $where = [
            'order_no' => $request->orderid,
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
            'userid' => $this->withdrawMerchantID,
            'orderid' => $order_no,
            'type' => 'bank',
            'amount' => intval($money),
            'notifyurl' => $this->withdrawal_callback_url,
            'returnurl' => env('SHARE_URL',''),
            'note' => 'recharge balance',
        ];
        $payload = [
            'cardname' => $withdrawalRecord->account_holder,
            'cardno' => $withdrawalRecord->bank_number,
            'bankid' => 10000,
            'bankname' => $withdrawalRecord->bank_name,
            'ifsc' => $withdrawalRecord->ifsc_code
        ];
        $params['payload'] = json_encode($payload);

        $params['sign'] = $this->generateSignRigorous($params,2);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('seven_india_withdrawalOrder',$params);
        $res = $this->requestService->postJsonData(self::$url_cashout . 'api/withdrawal', $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('seven_india_withdrawalOrder', [$res]);
        if ($res['success'] != 1) {
            $this->_msg = $res['message'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['ticket'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('seven_india_withdrawalCallback',$request->input());

        $pay_status = 0;
        if(isset($request->ispay)){
            if($request->ispay == 1){
                $pay_status= 1;
            }
        }
        if(isset($request->iscancel)){
            if($request->iscancel == 1){
                $pay_status= 3;
            }
        }
        if ($pay_status == 0) {
            $this->_msg = 'seven_india-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSignRigorous($params,2) <> $sign) {
            $this->_msg = 'seven_india-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->orderid,
            'plat_order_id' => $request->ticket,
            'pay_status' => $pay_status
        ];
        return $where;
    }

}
