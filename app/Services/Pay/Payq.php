<?php


namespace App\Services\Pay;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Payq extends PayStrategy
{

    protected static $url = 'https://payq.jinweiyule.com/api88779';    // 网关

    private  $recharge_callback_url = '';     // 充值回调地址
    private  $withdrawal_callback_url = '';  //  提现回调地址

    private $publicKey='zGKqlQN8dj1ike4JsVUYwBO6xpmWHunPATXFrfb0';  //公钥
    private $privateKey='7b7b4000a647d681ba195194b7fe3ceab4c7f7529a811acb112e9f92208247c384b732b5a8be6fbd04bc8f1de1bfb98d9cecabd87e9f8db08548af1c9726a5b02a7e0c2e056087a96315fddff645a2eae0665f361543dd4367f6d3e2a43d710f221a1aa9fa8dd82bfd8d319ba06de6a84bfe97e26eee6c5efc6fbb24dfb14c4dd4cfbf7b34314112795327b60cce5632dfce205e07cf612ad036d94135a49c66049f622db5016cef75c56d5624a71e15817d8d1a970285e617a6d93484f01ec2664b92671a3d48ed147e12ce5974dcf38ebb6b82bfeed7fbfd1d07eafb78377e34cf8221ddde2d62b740b740533f87d11b7df4517bcd59b5f64829a85c9025dc';  //私钥

    public $withdrawMerchantID;
    public $withdrawSecretkey;
    public $rechargeMerchantID;
    public $rechargeSecretkey;
    public $company = 'payq';   // 支付公司名

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

//        $this->publicKey = isset($rechargeConfig[$this->company])?$rechargeConfig[$this->company]['public_key']:"";
//        $this->privateKey = isset($rechargeConfig[$this->company])?$rechargeConfig[$this->company]['private_key']:"";

        $this->recharge_callback_url = self::$url_callback . '/api/recharge_callback' . '?type='.$this->company;
        $this->withdrawal_callback_url =  self::$url_callback . '/api/withdrawal_callback' . '?type='.$this->company;
    }

    /**
     * 生成签名  sign = Md5(key1=vaIue1&key2=vaIue2&key=签名密钥);
     */
    public  function generateSign(array $params)
    {
        return hash_hmac('sha256',http_build_query($params), $this->privateKey);
    }

    /**
     * 充值下单接口
     */
    public function rechargeOrder($pay_type, $money)
    {
        $order_no = self::onlyosn();
        $params = [
            'amt' => (float)$money,
            'apiversion' => 2,
            'eid' => $order_no,
            'mid' => (int)($this->rechargeMerchantID),
            'publickey' => $this->publicKey,
            'type' => 'recharge',
        ];
        $params['sign'] = $this->generateSign($params);
        $params['callback'] = $this->recharge_callback_url;

        \Illuminate\Support\Facades\Log::channel('mytest')->info('payq_rechargeOrder_data', [$params]);
        $res = $this->requestService->postFormData(self::$url, $params, [
            "content-type" => "application/x-www-form-urlencoded",
        ]);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('payq_rechargeOrder', ['res'=>$res]);
        if ($res['status'] != 1) {
            $this->_msg = $res['msg'];
            return false;
        }
        $native_url = $res['url'];
        $resData = [
            'out_trade_no' => $order_no,
            'pay_type' => $pay_type,
            'order_no' => $order_no,
            'native_url' => $native_url,
            'notify_url' => $this->recharge_callback_url,
            'pltf_order_id' => '',
            'verify_money' => '',
            'match_code' => '',
            'is_post' => isset($is_post)?$is_post:0,
            'params' => $params
        ];
        return $resData;
    }

    /**
     * 充值回调
     */
    function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('payq_rechargeCallback',$request->input());

        if ($request->status != 1)  {
            $this->_msg = 'payq-recharge-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        $remark = $params['remark'];
        unset($params['sign']);
        unset($params['type']);
        unset($params['remark']);
        if ($this->generateSign($params) <> $sign) {
            $this->_msg = 'payq-签名错误';
            return false;
        }

        $where = [
            'order_no' => $params['eid'],
            'pltf_order_id' => $params['uid']
        ];
        return $where;
    }

    /**
     *  后台审核请求提现订单 (提款)  代付方式
     */
    public function withdrawalOrder(object $withdrawalRecord)
    {
        ## IMPS代付统一下单
        $money = $withdrawalRecord->payment;    // 打款金额
//        $ip = $this->request->ip();
//        $order_no = self::onlyosn();
        $order_no = $withdrawalRecord->order_no;
        $signparam = [
            'acno' => $withdrawalRecord->bank_number,
            'amt' => (float)$money,
            'apiversion' => 2,
            'eid' => $order_no,
            'mid' => (int)($this->withdrawMerchantID),
            'publickey' => $this->publicKey,
            'type' => 'withdraw'
        ];
        $params['sign'] = $this->generateSign($signparam);
        $params['payeename'] = $withdrawalRecord->account_holder;
        $params['bankname'] = $withdrawalRecord->bank_name;
        $params['remarks'] = 'withdraw';
        $params['callback'] = $this->withdrawal_callback_url;
        $params['cmobile'] = $withdrawalRecord->phone;
        $params['cemail'] = $withdrawalRecord->email;
        $params['ifsc'] = $withdrawalRecord->ifsc_code;
//        $params['bcode'] = 'ALHB';
        $params['ip'] = request()->ip();

//        $options = array(
//            'http' => array(
//                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//                'method'  => 'POST',
//                'content' => http_build_query($signparam).'&'.http_build_query($params)
//            )
//        );
//        \Illuminate\Support\Facades\Log::channel('mytest')->info('payq_withdrawalOrder',$options);
//        $context  = stream_context_create($options);
//        $res = file_get_contents(self::$url,false, $context);
//        if(!$res){
//            $this->_msg = '代付申请失败';
//            return false;
//        }
//        $res = json_decode($res,true);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('payq_withdrawalOrder',array_merge($signparam,$params));
        $res = $this->requestService->postHttpBuildQuery(self::$url, array_merge($signparam,$params), ["content-type" => "application/x-www-form-urlencoded"]);
        if(!$res){
            $this->_msg = '代付申请失败';
            return false;
        }
        $res = json_decode($res,true);
        \Illuminate\Support\Facades\Log::channel('mytest')->info('payq_withdrawalOrder_rtn',[$res]);
        if ($res['status'] != 1) {
            $this->_msg = $res['msg'];
            return false;
        }
        return  [
            'pltf_order_no' => $res['uid'],
            'order_no' => $order_no
        ];
    }

    /**
     * 提现回调
     */
    function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('payq_withdrawalCallback',$request->input());

        $pay_status = 0;
        $status = (int)($request->status);
        switch($status){
            case 1:
                $pay_status = 1;
                break;
            case 2:
                $pay_status = 3;
                break;
            default:
                break;
        }

        if ($pay_status == 0) {
            $this->_msg = 'payq-withdrawal-交易未完成';
            return false;
        }
        // 验证签名
        $params = $request->input();
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['type']);
        unset($params['remark']);
        if ($this->generateSign($params) <> $sign) {
            $this->_msg = 'payq-签名错误';
            return false;
        }
        $where = [
            'order_no' => $params['eid'],
            'plat_order_id' => $params['eid'],
            'pay_status' => $pay_status
        ];
        $this->_msg = 1;
        return $where;
    }

}
