<?php


namespace App\Services\Pay\INDIA;


use App\Services\Pay\PayStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OceanPay extends PayStrategy
{

    protected static $url = 'https://www.oceanpay.in/api/outer/collections/addOrderByLndia';    // 支付网关

    protected static $url_cashout = 'https://www.oceanpay.in/api/outer/merwithdraw/addPaid'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'oceanpay';   // 支付公司名 -- 印度

    public $rechargeRtn = "OK";
    public $withdrawRtn = 'OK';

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
    }

    public function generateSign($params, $type=1)
    {
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if($value != '')
                $string[] = $key . '=' . $value;
        }
        $sign = (implode('&', $string)) . '&key=' .  $secretKey;
        return strtoupper(md5($sign));
    }

    public function generateRechargeSign($params)
    {
        $secretKey = $this->rechargeSecretkey;
        $signStr = sprintf('code=%s&merordercode=%s&notifyurl=%s&callbackurl=%s&amount=%d&key=%s',$params['code'], $params['merordercode'], $params['notifyurl'], $params['callbackurl'], $params['amount'], $secretKey);
        return strtoupper(md5($signStr));
    }

    public function generateRechargeCallbackSign($params)
    {
        $secretKey = $this->rechargeSecretkey;
        $signStr = sprintf('code=%s&key=%s&terraceordercode=%s&merordercode=%s&createtime=%s&chnltrxid=%s',$params['code'], $secretKey, $params['terraceordercode'], $params['merordercode'], $params['createtime'], $params['chnltrxid']);
        return strtoupper(md5($signStr));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $user = $this->getUser();
        $params = [
            'code' => $this->rechargeMerchantID,
            'notifyurl' => env('SHARE_URL',''),
            'amount' => intval($money),
            'callbackurl' => $this->recharge_callback_url,
            'merordercode' => $order_no,
        ];
        $params['signs'] = $this->generateRechargeSign($params);
        $params['paycode'] = 909;
        $params['starttime'] = time() * 1000;
        $params['ipaddr'] = $user->ip;
        $params['name'] = $user->phone;
        $params['mobile'] = $user->phone;
        $params['email'] = '88888888@gmail.com';
        \Illuminate\Support\Facades\Log::channel('mytest')->info('oceanpay_rechargeOrder', [$params]);
        $res = dopost(self::$url, http_build_query($params), []);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('oceanpay_rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "prepay failed";
            return false;
        }
        if($res['is'] != 1){
            $this->_msg = $res['msg'];
        }
        $native_url = $res['data']['checkstand'];
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => '',
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('oceanpay_rechargeCallback',$request->input());
        $params = $request->input();
        if ($params['returncode'] != '00')  {
            $this->_msg = 'oceanpay-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateRechargeCallbackSign($params) <> $sign) {
            $this->_msg = 'oceanpay-签名错误';
            return false;
        }
        $this->amount = $params['amount'];
        $where = [
            'order_no' => $params['merordercode'],
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
            'code' => $this->withdrawMerchantID,
            'amount' => intval($money),
            'bankname' => $withdrawalRecord->bank_name,
            'accountname' => $withdrawalRecord->account_holder,
            'cardnumber' => $withdrawalRecord->bank_number,
            'mobile' => $withdrawalRecord->phone,
            'email' => $withdrawalRecord->email,
            'starttime' => time() * 1000,
            'notifyurl' => $this->withdrawal_callback_url,
            'ifsc' => $withdrawalRecord->ifsc_code,
            'merissuingcode' => $order_no,
        ];

        $params['signs'] = $this->generateSign($params,2);
        $header[] = 'Content-Type: application/x-www-form-urlencoded';
        \Illuminate\Support\Facades\Log::channel('mytest')->info('oceanpay_withdrawal_params',$params);
        $res =dopost(self::$url_cashout, http_build_query($params), $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('oceanpay_withdrawal_return', [$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['msg'] != "success") {
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('oceanpay_withdrawalCallback',$request->input());
        $params = $request->input();
        $pay_status = 0;
        $status = $params['returncode'];
        if($status == 'SUCCESS'){
            $pay_status= 1;
        }
        if($status == 'FAIL'){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'oceanpay-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['signs'];
        unset($params['signs']);
        unset($params['type']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'oceanpay-签名错误';
            return false;
        }
        $where = [
            'order_no' =>$params['merissuingcode'],
            'plat_order_id' => $params['issuingcode'],
            'pay_status' => $pay_status
        ];
        return $where;
    }

}

