<?php


namespace App\Services\Pay;

use App\Repositories\Api\UserRepository;
use App\Services\RequestService;
use Illuminate\Http\Request;

abstract class PayStrategy
{

    public $_code = 200;
    public $_msg = "success";
    public $_data = [];

    protected $requestService;
    protected $request;
    protected $userRepository;

    protected static $url_callback  = '';    // 回调地址 (充值或提现)

    protected static $merchantID = '';     // 商户ID
    protected static $secretkey = '';      // 密钥

    public function __construct (
        RequestService $requestService,
        Request $request,
        UserRepository $userRepository
    )
    {
        $this->requestService = $requestService;
        $this->request = $request;
        $this->userRepository = $userRepository;

        self::$url_callback = env('APP_URL','');
        if (empty(self::$url_callback)) {
            die('请设置APP_URL');
        }

        self::$merchantID = env('PAY_MERCHANT_ID');
        self::$secretkey = env('PAY_SECRET_KEY');
        if (empty(self::$merchantID) || empty(self::$secretkey)) {
            die('请设置支付商户号和密钥');
        }
    }
    /**
     * 生成订单号
     */
    public static function onlyosn()
    {
        @date_default_timezone_set("Asia/Shanghai");
        $order_id_main = date('YmdHis') . rand(10000000, 99999999);
        //订单号码主体长度
        $order_id_len = strlen($order_id_main);
        $order_id_sum = 0;
        for ($i = 0; $i < $order_id_len; $i++) {
            $order_id_sum += (int)(substr($order_id_main, $i, 1));
        }
        //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
        $osn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT); //生成唯一订单号
        return $osn;
    }

    /**
     *  通过代付提款
     */
    function withdrawalOrderByDai(object $withdrawalRecord)
    {
//        $bank_name = $withdrawalRecord->bank_name;
        $account_holder = $withdrawalRecord->account_holder;
        $bank_number = $withdrawalRecord->bank_number;
        $ifsc_code = $withdrawalRecord->ifsc_code;
        $phone = $withdrawalRecord->phone;
        $email = $withdrawalRecord->email;

        // 各个支付独有的参数
        $onlyParams = [
            'bank_name' => $account_holder, // 收款姓名（类型为1,3不可空，长度0-200)
            'bank_card' => $bank_number,   // 收款卡号（类型为1,3不可空，长度9-26
            'ifsc' => $ifsc_code,   // ifsc代码 （类型为1,3不可空，长度9-26）
            'bank_tel' => $phone,   // 收款手机号（类型为3不可空，长度0-20）
            'bank_email' => $email,   // 收款邮箱（类型为3不可空，长度0-100）
        ];
        return $onlyParams;
    }

    /**
     * 请求充值下单接口
     * return array
        $resData = [
            'out_trade_no' => $order_no,
            'shop_id' => self::$merchantID,
            'pay_type' => $pay_type,
            'pltf_order_id' => $pltf_order_id,
            'native_url' => $res['data']['url'],
            'verify_money' => $verify_money,
            'match_code' => $match_code,
        ];
     */
    abstract function rechargeOrder($pay_type,$money);

    /**
     * 请求提现订单
     */
    abstract function withdrawalOrder(object $withdrawalRecord);

    /**
     * 充值回调
     * return array where条件
     */
    abstract function rechargeCallback(Request $request);

    /**
     * 提现回调
     */
    abstract function withdrawalCallback(Request $request);

}

