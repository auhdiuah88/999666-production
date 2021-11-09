<?php


namespace App\Services\Pay\BR;


use App\Services\Pay\PayStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WWWPay extends PayStrategy
{

    protected static $url = 'https://interface.sskking.com/pay/web';    // 支付网关

    protected static $url_cashout = 'https://interface.sskking.com/pay/transfer'; // 提现网关

    private $recharge_callback_url = '';     // 充值回调地址
    private $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;

    public $rechargeRtn = "success";
    public $withdrawRtn = 'success';

    public $company = 'brwwwpay';   // 支付公司名

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
            if(!empty($value))
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
            'version' => '1.0',
            'mch_id' => $this->rechargeMerchantID,
            'notify_url' => $this->recharge_callback_url,
            'page_url' => env('APP_URL',''),
            'mch_order_no' => $order_no,
            'pay_type' => 600,
            'trade_amount' => (string)intval($money),
            'order_date' => date('Y-m-d H:i:s'),
            'goods_name' => 'customer recharge',
        ];
        $params['sign'] = $this->generateSign($params);
        $params['sign_type'] = 'MD5';
        $header[] = "Content-Type: application/x-www-form-urlencoded";

        \Illuminate\Support\Facades\Log::channel('mytest')->info('wwwpay-rechargeOrder', [$params]);
        $res = dopost(self::$url, http_build_query($params), $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('wwwpay-rechargeOrder_return', [$res]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "prepay failed";
            return false;
        }
        if($res['respCode'] != 'SUCCESS'){
            $this->_msg = $res['tradeMsg'];
            return false;
        }
        if($res['tradeResult'] != 1){
            $this->_msg = 'request recharge failed';
            return false;
        }

        $native_url = $res['payInfo'];
        $resData = [
            'pay_type' => $pay_type,
            'out_trade_no' => $order_no,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => '',
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('wowpay-rechargeCallback', $request->post());
        $params = $request->post();
        if($params['tradeResult'] != 1) {
            $this->_msg = 'wowpay-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        unset($params['signType']);
        if ($this->generateSign($params) <> $sign) {
            $this->_msg = 'wowpay-签名错误';
            return false;
        }
        $this->amount = $params['amount'];
        $where = [
            'order_no' => $params['mchOrderNo'],
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
            'bank_code' => 'PIXPAY',
            'receive_name' => $withdrawalRecord->account_holder,
            'receive_account' => $withdrawalRecord->bank_number,
            'remark' => $withdrawalRecord->ifsc_code,
            'back_url' => $this->withdrawal_callback_url,
            'receiver_telephone' => $withdrawalRecord->phone,
            'document_type' => "CPF",
            'document_id' => $withdrawalRecord->bank_number,
        ];
        $params['sign'] = $this->generateSign($params,2);
        $params['sign_type'] = 'MD5';
        $header[] = "Content-Type: application/x-www-form-urlencoded";

        \Illuminate\Support\Facades\Log::channel('mytest')->info('wowpay-withdraw_params', $params);
        $res = dopost(self::$url_cashout, http_build_query($params), $header);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('wowpay-withdraw_return', [$res]);
        $res = json_decode($res,true);
        if (!$res) {
            $this->_msg = "提交代付失败";
            return false;
        }
        if ($res['respCode'] != 'SUCCESS') {
            $this->_msg = $res['errorMsg'];
            return false;
        }
        if ($res['tradeResult'] != 0) {
            $this->_msg = '代付请求失败';
            return false;
        }
        return [
            'pltf_order_no' => $res['tradeNo'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('wowpay-withdrawalCallback', $request->post());
        $params = $request->post();
        if ($params['status'] != 0) {
            $this->_msg = 'wowpay-withdrawal-交易未完成';
            return false;
        }
        $pay_status = 0;
        $status = (int)$params['tradeResult'];
        if ($status == 1) {
            $pay_status = 1;
        }
        if ($status == 2) {
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'wowpay-withdrawal-交易未完成';
            return false;
        }
        $data = $params['data'];
        // 验证签名
        $sign = $data['sign'];
        unset($data['sign']);
        unset($data['type']);
        unset($data['signType']);
        if ($this->generateSign($data,2) <> $sign) {
            $this->_msg = 'wowpay-签名错误';
            return false;
        }
        $where = [
            'order_no' => $data['merTransferId'],
            'plat_order_id' => $params['tradeNo'],
            'pay_status' => $pay_status
        ];
        return $where;
    }

}
