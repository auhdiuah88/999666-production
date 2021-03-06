<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HuiZhong extends PayStrategy
{

    protected static $url = 'https://goobal.gdsua.com/';    // 支付网关

    protected static $url_cashout = 'https://yugob.gdsua.com/'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'huizhong';   // 支付公司名

    public $withdrawRtn = "SUCCESS";
    public $rechargeRtn = "SUCCESS";

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
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if($value != '')
                $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $secretKey;
        return md5($sign);
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'mer_no' => $this->rechargeMerchantID,
            'mer_order_no' => $order_no,
            'pname' => 'ZhangSan',
            'pemail' => '11111111@qq.com',
            'phone' => 15988888888,
            'order_amount' => intval($money),
            'countryCode' => 'IND',
            'ccy_no' => 'INR',
            'busi_code' => '100303',
            'goods' => 'recharge balance',
            'notifyUrl' => $this->recharge_callback_url,
            'pageUrl' => env('SHARE_URL',''),
        ];
        $params['sign'] = $this->generateSign($params,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('HZ_rechargeOrder', [$params]);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        $res =dopost(self::$url . 'ty/orderPay', $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('HZ_rechargeOrderReturn', [$res]);
//        $res = $this->requestService->postJsonData(self::$url . 'ty/orderPay' , $params);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "prepay failed";
            return false;
        }
        if ($res['status'] != 'SUCCESS') {
            \Illuminate\Support\Facades\Log::channel('mytest')->info('HZ_rechargeOrder_return', $res);
            $this->_msg = $res['err_msg'];
            return false;
        }
        $native_url = $res['order_data'];

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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('HZ_rechargeCallback',$request->post());

        if ($request->status != 'SUCCESS')  {
            $this->_msg = 'HZ-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,1) <> $sign) {
            $this->_msg = 'HZ-签名错误';
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
        $params = [
            'mer_no' => $this->withdrawMerchantID,
            'mer_order_no' => $order_no,
            'acc_no' => $withdrawalRecord->bank_number,
            'acc_name' => $withdrawalRecord->account_holder,
            'ccy_no' => 'INR',
            'order_amount' => intval($money),
            'bank_code' => 'IDPT0001',
            'summary' => 'Balance Withdrawal',
            'province' => $withdrawalRecord->ifsc_code,
            'notifyUrl' => $this->withdrawal_callback_url
        ];
        $params['sign'] = $this->generateSign($params,2);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('HZ_withdrawalOrder',$params);
        $res =dopost(self::$url_cashout . 'withdraw/singleOrder', $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('HZ_withdrawalOrder',[$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['status'] != 'SUCCESS') {
            $this->_msg = $res['err_msg'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['order_no'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('HZ_withdrawalCallback',$request->post());

        $pay_status = 0;
        $status = (string)($request->status);
        if($status == 'SUCCESS'){
            $pay_status= 1;
        }
        if($status == 'FAIL'){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'HZ-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'HZ-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->mer_order_no,
            'plat_order_id' => $request->order_no,
            'pay_status' => $pay_status
        ];
        return $where;
    }

}
