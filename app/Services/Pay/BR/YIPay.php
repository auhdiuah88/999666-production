<?php


namespace App\Services\Pay\BR;


use App\Services\Pay\PayStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class YIPay extends PayStrategy
{

    protected static $url = 'https://api.bankspay.com:8888/api/v1/topuppix';    // 支付网关

    protected static $url_cashout = 'https://api.bankspay.com:8888/api/v1/withdraw'; // 提现网关

    private $recharge_callback_url = '';     // 充值回调地址
    private $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;

    public $rechargeRtn = "";
    public $withdrawRtn = '';

    public $company = 'yipay';   // 支付公司名

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

    public function generateSign($amount, $type=1)
    {
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        $merchantId = $type == 1 ? $this->rechargeMerchantID : $this->withdrawMerchantID;
        $sign_str = $secretKey . $merchantId . $amount;
        return strtolower(md5($sign_str));
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'partnerid' => $this->rechargeMerchantID,
            'amount' => sprintf("%.2f",intval($money)),
            'notifyurl' => $this->recharge_callback_url,
            'partnerorder' => $order_no,
            'remark' => 'recharge balance',
        ];
        $params['sign'] = $this->generateSign($params['amount']);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('yipay-rechargeOrder', [$params]);
        $res = dopost(self::$url, $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('yipay-rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "prepay failed";
            return false;
        }
        if($res['status'] != 200){
            $this->_msg = "prepay failed .";
            return false;
        }

        $native_url = $res['RequestUrl'];
        $resData = [
            'pay_type' => $pay_type,
            'out_trade_no' => $order_no,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => $res['Orderid'],
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('yipay-rechargeCallback', $request->post());
        $params = $request->post();
        if($params['category'] != 0){  //0.支付回调 1 代付回调
            $this->_msg = 'yipay-recharge-回调类型错误';
            return false;
        }
        if ($params['status'] != 1)  {
            $this->_msg = 'yipay-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        if ($this->generateSign($params['amount']) <> $sign) {
            $this->_msg = 'yipay-签名错误';
            return false;
        }
        $this->amount = $params['amount'];
        $where = [
            'order_no' => $params['PartnerOrder'],
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
            'partnerid' =>  $this->withdrawMerchantID,
            'orderid' => $order_no,
            'amount' => sprintf("%.2f",intval($money)),
            'cardnumber' => $withdrawalRecord->bank_number,  //pix
            'accountname' => $withdrawalRecord->account_holder,
            'notifyurl' => $this->withdrawal_callback_url,
            'remark' => 'customer withdraw',
        ];
        $params['sign'] = $this->generateSign($params['amount'],2);
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('yipay-withdraw_params', $params);
        $res = dopost(self::$url_cashout, $params_string, $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('yipay-withdraw_return', [$res]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "提交代付失败";
            return false;
        }
        if ($res['status'] != 1) {
            $this->_msg = $res['msg'];
            return false;
        }
        return [
            'pltf_order_no' => '',
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
        $status = (int)$params['status'];
        if ($status == 1) {
            $pay_status = 1;
        }
        if ($status == 2) {
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'yipay-withdrawal-交易未完成';
            return false;
        }
        $data = $params['data'];
        // 验证签名
        $sign = $data['sign'];
        if ($this->generateSign($data['amount'],2) <> $sign) {
            $this->_msg = 'yipay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $data['orderid'],
            'plat_order_id' => '',
            'pay_status' => $pay_status
        ];
        return $where;
    }
}
