<?php


namespace App\Services\Api;


use App\Common\Common;
use App\Repositories\Api\RechargeRepository;
use App\Repositories\Api\UserRepository;
use App\Services\Library\Auth;
use App\Services\Library\Netease\IM;
use App\Services\Library\Netease\SMS;
use App\Services\Library\Upload;
use App\Services\RequestService;
use Illuminate\Support\Facades\DB;

class RechargeService extends \App\Services\BaseService
{
    private $userRepository;
    private $rechargeRepository;
    private $requestService;

    protected static $url = 'http://ipay-in.yynn.me/pay';
    protected static $merchantID = 10175;
    protected static $secretkey = '1hmoz1dbwo2xbrl3rei78il7mljxdhqi';

    public function __construct(UserRepository $userRepository,
                                RechargeRepository $rechargeRepository,
                                RequestService $requestService
    )
    {
        $this->userRepository = $userRepository;
        $this->rechargeRepository = $rechargeRepository;

        $this->requestService = $requestService;
    }

    /**
     * 充值请求生成充值订单
     */
    public function rechargeOrder($request)
    {
        $user_id = $this->getUserId($request->header("token"));
        $user = $this->userRepository->findByIdUser($user_id);
        $pay_type = $request->pay_type;
        $money = $request->money;
        $order_no = $this->onlyosn();
        $params = [
            'api_name' => 'quickpay.all.native',
            'money' => $money,
            'notify_url' => url('api/recharge_callback'),
            'order_des' => '支付充值',
            'out_trade_no' => $order_no,
            'shop_id' => self::$merchantID,
        ];
        $params['sign'] = self::generateSign($params);

        $res = $this->requestService->postJsonData(self::$url, $params);
        if ($res['rtn_code'] <> 1000) {
            $this->_msg = $res['rtn_msg'];
            return false;
        }
        $this->rechargeRepository->addRechargeLog($user, $money, $order_no, $pay_type, $res['pltf_order_id'], $res['native_url'],
                                $res['verify_money'], $res['match_code'], $params['sign']);
        return $res;
    }

    /**
     * 充值记录
     */
    public function rechargeLog($request)
    {
        return $this->rechargeRepository->getRechargeLogs($request->status, $request->limit, $request->page);
    }

    /**
     * 生成签名   sign = Md5(key1=vaIue1&key2=vaIue2…商户密钥);
     */
    private static function generateSign(array $params)
    {
        ksort($params);
        $string = [];
        foreach ($params as $key => $value) {
            $string[] = $key . '=' . $value;
        }
        $sign = strtolower(implode('&', $string)) . self::$secretkey;
        return md5($sign);
    }


    /**
     * 生成订单号
     */
    private function onlyosn()
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
     *  充值回调
     */
    public function rechargeCallback($request)
    {
        /** {
             * "api_name": "quickpay.all.native.callback",
             * "money": "500",
             * "order_des": "充值",
             * "out_trade_no": "202010271647290000000001",
             * "pay_result": "success",
             * "pltf_order_id": "9843202010271647304254",
             * "shop_id": "10164",
             * "sign": "3e124d9265284e06d9563aeb54302f6f"
         * }
         */
        if ($request->shop_id <> self::$merchantID
            || $request->api_name <> 'quickpay.all.native.callback'
            || $request->pay_result <> 'success'
        ){
            $this->_msg = '参数错误';
            return false;
        }

        // 充值成功
        $money = $request->money;
        $where = [
            'order_no' => $request->out_trade_no,
            'pltf_order_id' => $request->pltf_order_id,
//            'money' => $money
        ];
        $rechargeLog = $this->rechargeRepository->getRechargeInfoByCondition($where);
        if (!$rechargeLog) {
            $this->_msg = '找不到此订单';
            return false;
        }

        DB::beginTransaction();
        try {
            $user = $this->userRepository->findByIdUser($rechargeLog->user_id);

            if ($user->is_first_recharge == 0 && $money >= 200) {
                $user->is_first_recharge = 1; // 是否第一次充值

                $referrensUser = $this->userRepository->findByIdUser($user->two_recommend_id);  // 给推荐我注册的人，推荐充值数加1
                if ($referrensUser) {
                    $referrensUser->rec_ok_count += 1;
                    $referrensUser->save();
                }
            }

            $dq_balance = $user->balance;    // 当前余额
            $wc_balance = bcadd($dq_balance, $money, 2);   // 变动后余额

            $user->balance = $wc_balance;
            $user->total_recharge = bcadd($user->total_recharge, $money, 2);

            // 记录充值成功余额变动
            $this->userRepository->updateRechargeBalance($user, $money);

            // 更新充值成功记录
            $this->rechargeRepository->updateRechargeLog($rechargeLog, 2, $money);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
//            $this->rechargeRepository->updateRechargeLog($rechargeLog, 3, $money);
            $this->_msg = $e->getMessage();
            return false;
        }
        return true;
    }

}

