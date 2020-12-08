<?php


namespace App\Services\Pay;

use App\Repositories\Api\UserRepository;
use App\Services\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

abstract class PayStrategy
{
    public $_code = 200;
    public $_msg = "success";
    public $_data = [];

    protected $requestService;
    protected $request;
    protected $userRepository;

    protected static $url_callback  = '';    // 回调地址 (充值或提现)

//    public  $merchantID = '';     // 商户ID
//    public  $secretkey = '';      // 密钥

    public function __construct (
        RequestService $requestService,
        Request $request,
        UserRepository $userRepository
    )
    {
        $this->requestService = $requestService;
        $this->request = $request;
        $this->userRepository = $userRepository;

        // 充值和提现回调host
        self::$url_callback = env('APP_URL','');
        if (empty(self::$url_callback) || Str::contains(self::$url_callback,'localhost')) {
            die('请设置.env的APP_URL');
        }

        // 用于子类初始化操作
        $this->_initialize();
    }

    /**
     * 用于子类初始化操作
     * @access protected
     */
    protected function _initialize()  {}

    /**
     * 根据token获取当前用户
     */
    public function getUserId()
    {
        $token = $this->request->header('token');
        $token = urldecode($token);
        list($user_id,$time) = explode("+", Crypt::decrypt($token));
        return $user_id;
    }
    /**
     * 根据token获取当前用户
     */
    public function getUser()
    {
        $token = $this->request->header('token');
        $token = urldecode($token);
        list($user_id,$time) = explode("+", Crypt::decrypt($token));
        return $this->userRepository->findByIdUser($user_id);
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

