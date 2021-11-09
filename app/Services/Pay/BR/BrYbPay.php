<?php


namespace App\Services\Pay\BR;


use App\Services\Pay\PayStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrYbPay extends PayStrategy
{

    protected static $url = 'http://polymerizations.com/poi/pay/index/PayOrderCreate';    // 支付网关

    protected static $url_cashout = 'http://polymerizations.com/poi/dai/index/DaiOrderCreate'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'brybpay';   // 支付公司名 -- 巴西

    public $rechargeRtn = "success";
    public $withdrawRtn = 'success';

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
        return strtolower(md5($params['mer_no'] . $params['mer_order_no'] . $secretKey));
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
            'pname' => 'zhangsan',
            'pemail' => '123123123@gmail.com',
            'phone' => '88888888',
            'order_amount' => intval($money),
            'country_code' => 'BRA',
            'cyy_no' => 'BRL',
            'pay_type' => 'PIX',
            'notify_url' => $this->recharge_callback_url,
            'callback_url' => env('SHARE_URL',''),
        ];
        $params['sign'] = $this->generateSign($params);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ybpay_rechargeOrder', [$params]);
        $res =dopost(self::$url, $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ybpay_rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "prepay failed";
            return false;
        }
        if($res['code'] != 1){
            $this->_msg = $res['msg'];
            return false;
        }
        $native_url = $res['pay_url'];
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['order_number'],
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ybpay_rechargeCallback',$request->input());
        $params = $request->input();
        if($params['code'] != 1){
            $this->_msg = 'YB-recharge-接口状态异常';
            return false;
        }
        if ($params['order_status'] != 4)  {
            $this->_msg = 'YB-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params) <> $sign) {
            $this->_msg = 'YB-签名错误';
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
            'order_amount' => intval($money),
            'pay_type' => 'PIX',
            'cyy_no' => 'BRL',
            'acc_no' => $withdrawalRecord->bank_number,
            'acc_name' => $withdrawalRecord->account_holder,
            'province' => $withdrawalRecord->ifsc_code,
            'summary' => 'customer withdraw',
            'notifyurl' => $this->withdrawal_callback_url,
        ];

        $params['sign'] = $this->generateSign($params,2);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ybpay_withdrawal_params',$params);
        $res =dopost(self::$url_cashout, $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ybpay_withdrawal_return', [$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = '提交代付失败';
            return false;
        }
        if ($res['code'] != 1) {
            $this->_msg = $res['msg'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['order_number'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('yb_withdrawalCallback',$request->input());
        $params = $request->input();
        if($params['code'] != 1){
            $this->_msg = 'yb-withdrawal-接口状态异常';
            return false;
        }
        $pay_status = 0;
        $status = $params['order_status'];
        if($status == 4){
            $pay_status= 1;
        }
        if($status == 3 || $status == 2 || $status == -1){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'yb-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'yb-签名错误';
            return false;
        }
        $where = [
            'order_no' =>$params['mer_order_no'],
            'plat_order_id' => $params['order_no'],
            'pay_status' => $pay_status
        ];
        return $where;
    }

}

