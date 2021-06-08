<?php


namespace App\Services\Pay;

use App\Repositories\Api\UserRepository;
use App\Services\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 *  如：unicasino.in  的充值和提现类
 */
class YLpay extends PayStrategy
{

    protected static $rechargeUrl = 'https://pay.yarlungpay.com/';

    protected static $withdrawUrl = 'https://pay.yarlungpay.com/';

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'ylpay';   // 支付公司名

    public $rechargeRtn='success'; //支付成功的返回
    public $withdrawRtn='success'; //提现成功的返回

    public function _initialize()
    {
//        self::$merchantID = config('pay.company.'.$this->company.'.merchant_id');
//        self::$secretkey = config('pay.company.'.$this->company.'.secret_key');
//        if (empty(self::$merchantID) || empty(self::$secretkey)) {
//            die('请设置 ipay 支付商户号和密钥');
//        }
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
     * 生成签名   sign = Md5(key1=vaIue1&key2=vaIue2…商户密钥);
     */
    public function generateSign(array $params, $type=1)
    {
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            if($value != '')
                $string[] = $key . '=' . $value;
        }
        $string[] = 'key=' . $secretKey;
        $sign = (implode('&', $string));
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ylpay_rechargeOrder_sign', [$sign]);
        return strtolower(md5($sign));
    }

    /**
     * 充值下单接口
     */
    function rechargeOrder($pay_type,$money)
    {
        $order_no = self::onlyosn();
        $params = [
            'mch_id' => $this->rechargeMerchantID,
            'notify_url' => $this->recharge_callback_url,
            'page_url' => env('SHARE_URL',''),
            'mch_order_no' => $order_no,
            'pay_type' => '102',
            'trade_amount' => (string)intval($money),
            'order_date' => date("Y-m-d H:i:s"),
            'goods_name' => 'balance recharge',
        ];
        if(!$params['page_url'])unset($params['page_url']);
        $params['sign'] = $this->generateSign($params,1);
        $params['sign_type'] = 'MD5';

        \Illuminate\Support\Facades\Log::channel('mytest')->info('ylpay_rechargeOrder', $params);

//        $res = $this->requestService->postJsonData(self::$rechargeUrl . 'sepro/pay/web', $params);
//        if ($res['rtn_code'] <> 1000) {
//            \Illuminate\Support\Facades\Log::channel('mytest')->info('sepro_rechargeOrder_return', $res);
//            $this->_msg = $res['rtn_msg'];
////            $this->_data = $res;
//            return false;
//        }
        $resData = [
            'out_trade_no' => $order_no,
            'shop_id' => $this->rechargeMerchantID,
            'pay_company' => $this->company,
            'pay_type' => $pay_type,
            'native_url' => self::$rechargeUrl . 'pay/web',
            'pltf_order_id' => '',
            'verify_money' => $params['trade_amount'],
            'match_code' => '',
            'notify_url' => $this->recharge_callback_url,
            'params' => $params,
            'is_post' => 2,
        ];
        return $resData;
    }

    function withdrawalOrder(object $withdrawalRecord)
    {
        $money = $withdrawalRecord->payment;    // 打款金额
//        $ip = $this->request->ip();
//        $order_no = self::onlyosn();
        $order_no = $withdrawalRecord->order_no;
        $params = [
            'mch_id' => $this->withdrawMerchantID,
            'mch_transferId' => $order_no,
            'currency' => 'INR',
            'transfer_amount' => intval($money),
            'apply_date' => date('Y-m-d H:i:s'),
            'bank_code' => 'IDPT0001',
            'receive_name' => $withdrawalRecord->account_holder,
            'receive_account' => $withdrawalRecord->bank_number,
            'remark' => $withdrawalRecord->ifsc_code,
            'back_url' => $this->withdrawal_callback_url,
        ];
        $params['sign'] = $this->generateSign($params,2);
        $params['sign_type'] = 'MD5';
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ylpay_withdrawalOrder',$params);
        $res = $this->requestService->postFormData(self::$withdrawUrl . 'pay/transfer', $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ylpay_withdrawalOrder_rtn',$res);
        if($res['respCode'] != 'SUCCESS'){
            $this->_msg = $res['errorMsg'];
            return false;
        }
        if($res['tradeResult'] == '3' || $res['tradeResult'] == '2'){
            $this->_msg = '代付订单被拒绝';
            return false;
        }
        return  [
            'pltf_order_no' => $res['tradeNo'],
            'order_no' => $order_no
        ];
    }

    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ylpay_rechargeCallback',$request->post());

        if ($request->tradeResult != '1')  {
            $this->_msg = 'sepro-recharge-交易未完成';
            return false;
        }

        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['signType']);
        if ($this->generateSign($params,1) <> $sign){
            $this->_msg = '签名错误';
            return false;
        }
        $this->amount = $params['amount'];
        $where = [
            'order_no' => $request->mchOrderNo,
            'pltf_order_id' => $request->orderNo,
        ];

        return $where;
    }

    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('ylpay_withdrawalCallback',$request->post());

        $pay_status = 0;
        $status = (string)($request->tradeResult);
        if($status == '1'){
            $pay_status= 1;
        }
        if($status == '2' || $status == '3'){
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'ylpay_withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['signType']);
        if ($this->generateSign($params,2) <> $sign) {
            $this->_msg = 'ylpay_签名错误';
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

