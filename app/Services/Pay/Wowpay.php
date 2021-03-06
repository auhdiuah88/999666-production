<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Wowpay extends PayStrategy
{

    protected static $url = 'https://interface.sskking.com/pay/web';    // 支付网关

    protected static $url_cashout = 'https://interface.sskking.com/pay/transfer'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;

    public $rechargeRtn = 'success';
    public $withdrawRtn = 'success';

    public $company = 'WOWpay';   // 支付公司名

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

    protected $rechargeTypeList = [
        '1' => '122',
        '2' => '102',
    ];

    /**
     * 生成签名  sign = Md5(key1=vaIue1&key2=vaIue2&key=签名密钥);
     */
    public  function generateSign(array $params, $type=1)
    {
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if(!empty($value))
                $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $secretKey;
        \Illuminate\Support\Facades\Log::channel('mytest')->info('WOW_sign', [$sign]);
        return strtolower(md5($sign));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'version' => '1.0',
            'mch_id' => $this->rechargeMerchantID,
            'notify_url' => $this->recharge_callback_url,
            'page_url' => env('SHARE_URL',''),
            'mch_order_no' => $order_no,
            'pay_type' => $this->rechargeTypeList[$this->rechargeType],
            'trade_amount' => (string)intval($money),
            'order_date' => date('Y-m-d H:i:s'),
            'goods_name' => 'customer recharge',
        ];
        $params['sign'] = $this->generateSign($params,1);
        $params['sign_type'] = 'MD5';
        \Illuminate\Support\Facades\Log::channel('mytest')->info('WOW_rechargeParams', [$params]);
        $header[] = "Content-Type: application/x-www-form-urlencoded";
        $res = dopost(self::$url, http_build_query($params), $header);
//        $res = $this->requestService->postFormData(self::$url , $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('WOW_rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if(!$res)
        {
            $this->_msg = 'request recharge failed.';
            return false;
        }
        if ($res['respCode'] != 'SUCCESS') {
            $this->_msg = $res['tradeMsg'];
            return false;
        }
        if($res['tradeResult'] != 1){
            $this->_msg = 'request recharge failed';
            return false;
        }
        $native_url = $res['payInfo'];

        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['orderNo'],
            'verify_money' => $res['oriAmount'],
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('WOW_rechargeCallback',$request->post());

        if ($request->tradeResult != 1)  {
            $this->_msg = 'WOW-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        unset($params['signType']);
        if ($this->generateSign($params,1) <> $sign) {
            $this->_msg = 'WOW-签名错误';
            return false;
        }
        $this->amount = $request->amount;
        $where = [
            'order_no' => $request->mchOrderNo,
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
            'mch_id' => $this->withdrawMerchantID,
            'mch_transferId' => $order_no,
            'transfer_amount' => (string)intval($money),
            'apply_date' => date('Y-m-d H:i:s'),
            'bank_code' => 'IDPT0001',
            'receive_name' => $withdrawalRecord->account_holder,
            'receive_account' => $withdrawalRecord->bank_number,
            'remark' => $withdrawalRecord->ifsc_code,
            'back_url' => $this->withdrawal_callback_url,
            'receiver_telephone' => $withdrawalRecord->phone,
        ];
        $params['sign'] = $this->generateSign($params,2);
        $params['sign_type'] = 'MD5';
        \Illuminate\Support\Facades\Log::channel('mytest')->info('WOW_withdrawalParams',$params);
        $res = $this->requestService->postFormData(self::$url_cashout, $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('WOW_withdrawalReturn',[$res]);
        if ($res['respCode'] != 'SUCCESS') {
            $this->_msg = $res['errorMsg'];
            return false;
        }
        if ($res['tradeResult'] != 0) {
            $this->_msg = '代付请求失败';
            return false;
        }
        return  [
            'pltf_order_no' => $res['tradeNo'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('WOW_withdrawalCallback',$request->post());

        $pay_status = 0;
        $status = (string)($request->tradeResult);
        if($status == 1){
            $pay_status= 1;
        }
        if($status == 2){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'WOW-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        unset($params['signType']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'WOW-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->merTransferId,
            'plat_order_id' => $request->tradeNo,
            'pay_status' => $pay_status
        ];
        return $where;
    }

}
