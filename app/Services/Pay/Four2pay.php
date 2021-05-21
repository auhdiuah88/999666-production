<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Four2pay extends PayStrategy
{

    protected static $url = 'https://four2.beslink.co/';    // 支付网关

    protected static $url_cashout = 'https://four2.beslink.co/'; // 提现网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;

    public $rechargeRtn = 'success';
    public $withdrawRtn = 'success';

    public $company = 'Four2';   // 支付公司名

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
    public  function generateSign($params, $type=1)
    {
        $secretKey = $type == 1 ? $this->rechargeSecretkey : $this->withdrawSecretkey;
        $str = '';
        foreach($params as $val){
            $str .= $val;
        }
        $str .= $secretKey;
//        \Illuminate\Support\Facades\Log::channel('mytest')->info('four2_rechargeParamsStr', [$str]);
        return md5($str);
    }

    /**
     * 生成签名  sign = Md5(key1=vaIue1&key2=vaIue2&key=签名密钥);
     */
    public  function generateSign2(array $params, $type=2)
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

    protected function makeNative($params, $url)
    {
        $url .= "?";
        foreach($params as $key => $item){
            $url .= $key . "=" . $item . "&";
        }
        return trim($url,'&');
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'u' => $this->rechargeMerchantID,
            'id' => $order_no,
            'je' => $money * 100,
            'sp' => urlencode("customer recharge"),
            'cb' => urlencode($this->recharge_callback_url),
            'pm' => 'c1401',
            'json' => 1,
        ];
        $signParam = [
            'u' => $params['u'],
            'id' => $params['id'],
            'je' => $params['je'],
            'sp' => urldecode($params['sp'])
        ];
        $params['sign'] = $this->generateSign($signParam,1);

        \Illuminate\Support\Facades\Log::channel('mytest')->info('four2_rechargeParams', [$params]);
        $url = $this->makeNative($params, self::$url . 'pay_index.php');
        \Illuminate\Support\Facades\Log::channel('mytest')->info('four2_rechargeOrder_url', [$url]);
        $res = $this->requestService->get( $url, []);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('four2_rechargeOrder_return', [$res]);
        if ($res['status'] != 1) {
            $this->_msg = $res['message'];
            return false;
        }
        $native_url = $res['data']['pay_url'];
//        $native_url = $this->makeNative($params, self::$url . 'pay_index.php');

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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('four2_rechargeCallback',$request->input());

        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        $signParam = [
            'ordered' => $params['orderid'],
            'amount' => $params['amount'],
            'payno' => $params['payno'],
        ];
        if ($this->generateSign($signParam,1) <> $sign) {
            $this->_msg = 'four-签名错误';
            return false;
        }
        $this->amount = $request->amount / 100;
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
            'uid' => $this->withdrawMerchantID,
            'thirdId' => $order_no,
            'amount' => $money * 100,
            'name' => $withdrawalRecord->account_holder,
            'account' => $withdrawalRecord->bank_number,
            'ifsc' => $withdrawalRecord->ifsc_code,
            'callback' => $this->withdrawal_callback_url,
            'pay_code' => 'c1704',
            'bank' => $withdrawalRecord->bank_name,
        ];
        $params['sign'] = $this->generateSign2($params,2);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('four2_withdrawalParams',$params);
        $res = $this->requestService->postJsonData(self::$url_cashout . 'df_query.php', $params);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('four2_withdrawalReturn',[$res]);
        if ($res['status'] != 1) {
            $this->_msg = $res['message'];
            return false;
        }
        if ($res['data'] != 'ok') {
            $this->_msg = '代付请求失败';
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
        \Illuminate\Support\Facades\Log::channel('mytest')->info('four_withdrawalCallback',$request->input());

        $pay_status = 0;
        $status = (string)($request->stat);
        if($status == 1){
            $pay_status= 1;
        }else{
            $pay_status = 3;
        }
        if ($pay_status == 0) {
            $this->_msg = 'four-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        unset($params['signType']);
        if ($this->generateSign2($params,2) <> $sign) {
            $this->_msg = 'four-签名错误';
            return false;
        }
        $where = [
            'order_no' => $request->orderid,
            'plat_order_id' => $request->payno,
            'pay_status' => $pay_status
        ];
        return $where;
    }

}
