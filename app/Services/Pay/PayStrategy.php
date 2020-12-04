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
    public function __construct (
        RequestService $requestService,
        Request $request,
        UserRepository $userRepository
    )
    {
        $this->requestService = $requestService;
        $this->request = $request;
        $this->userRepository = $userRepository;
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
    abstract function withdrawalOrder(Request $request);

    /**
     * 充值回调
     * return array where条件
     */
    abstract function rechargeCallback(Request $request);

}

