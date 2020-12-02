<?php


namespace App\Services\Api;

use App\Common\Common;
use App\Repositories\Api\RechargeRepository;
use App\Repositories\Api\UserRepository;
use App\Repositories\Api\WithdrawalRepository;
use App\Services\Library\Auth;
use App\Services\Library\Netease\IM;
use App\Services\Library\Netease\SMS;
use App\Services\Library\Upload;
use App\Services\PayService;
use App\Services\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RechargeService extends PayService
{
    private $userRepository;
    private $rechargeRepository;
    private $withdrawalRepository;
    private $requestService;

    public function __construct(UserRepository $userRepository,
                                RechargeRepository $rechargeRepository,
                                WithdrawalRepository $withdrawalRepository,
                                RequestService $requestService
    )
    {
        $this->userRepository = $userRepository;
        $this->rechargeRepository = $rechargeRepository;
        $this->withdrawalRepository = $withdrawalRepository;

        $this->requestService = $requestService;
    }

    /**
     * 充值下单接口-通用
     * POST方式
     */
    public function rechargeOrder(Request $request)
    {
        $user_id = $this->getUserId($request->header("token"));
        $user = $this->userRepository->findByIdUser($user_id);
        $pay_type = $request->pay_type;
        $money = $request->money;
        $order_no = $this->onlyosn();
        $pay_type = 1;
        $params = [
            'mch_id' => self::$merchantID,
            'ptype' => $pay_type,       // Paytm支付：1     银行卡：3
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => '充值',
            'client_ip' => $request->ip(),
            'format' => 'https://www.baidu.com',
            'notify_url' => url('api/recharge_callback'),
            'time' => time(),
        ];
        $params['sign'] = self::generateSign($params);
        $res = $this->requestService->postJsonData(self::$url . '/order/place', $params,'body');
        dd($res);
//        if ($res['rtn_code'] <> 1000) {
//            $this->_msg = $res['rtn_msg'];
//            $this->_data = $res;
//            return false;
//        }
        $this->rechargeRepository->addRechargeLog($user, $money, $order_no, $pay_type, $res['pltf_order_id'], $res['native_url'],
            $res['verify_money'], $res['match_code'], $params['sign']);
        return $res;
    }

    /***
     * 充值下单接口-JSON封装请求
     * GET方式
     */
    public function rechargeOrder2(Request $request)
    {
        $user_id = $this->getUserId($request->header("token"));
        $user = $this->userRepository->findByIdUser($user_id);
        $pay_type = $request->pay_type;
        $money = $request->money;
        $order_no = $this->onlyosn();
        $pay_type = 1;
        $params = [
            'mch_id' => self::$merchantID,
            'ptype' => $pay_type,
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => '充值',
            'client_ip' => $request->ip(),
            'format' => 'https://www.baidu.com',
            'notify_url' => url('api/recharge_callback'),
            'time' => time(),
        ];
        $params['sign'] = self::generateSign($params);
        $params = urlencode(json_encode($params));
        $res = $this->requestService->get(self::$url . '/order/getUrl?json='.$params);
        dd($res);
        if ($res['rtn_code'] <> 1000) {
            $this->_msg = $res['rtn_msg'];
            $this->_data = $res;
            return false;
        }
        $pltf_order_id = '123456';
        $native_url = '';
//        $this->rechargeRepository->addRechargeLog($user, $money, $order_no, $pay_type, $pltf_order_id, $native_url,
//            $res['verify_money'], $res['match_code'], $params['sign']);
        $this->rechargeRepository->addRechargeLog($user, $money, $order_no, $pay_type);
        return $res;
    }

    /**
     * 充值下单接口-跳转选择支付类型页面
     */
    public function rechargeTypeSelect(Request $request){
        $money = $request->money;
        $order_no = $this->onlyosn();
        $params = [
            'mch_id' => self::$merchantID,
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => '充值',
            'client_ip' => $request->ip(),
            'format' => 'https://www.baidu.com',
            'notify_url' => url('api/recharge_callback'),
            'time' => time(),
        ];
        $params['sign'] = self::generateSign($params);
        $res = $this->requestService->postJsonData(self::$url . '/order/placeForIndex',$params);
        dd($res);
    }

    /**
     * 充值订单查询
     */
    public function orderQuery($order_no,$pltf_order_id = '')
    {
        $params = [
            'mch_id' => self::$merchantID,
            'out_order_sn' => $order_no,
            'time' => time(),
        ];
        $params['sign'] = self::generateSign($params);
        return $this->requestService->postJsonData(self::$url . '/order/query', $params);
    }

    /**
     *  充值回调
     *
     * 请求参数	参数名	数据类型	可空	说明
        商户单号	sh_order	string	否	商户系统的业务单号
        平台单号	pt_order	string	否	支付平台的订单号
        订单金额	 money	float	否	与支付提交的金额一致
        支付完成时间	time	int	否	系统时间戳UTC秒（10位）
        订单状态	state	int	否	订单状态
        0 已提交       1 已接单
        2 超时补单     3 订单失败
        4 交易完成     5 未接单
        商品描述	goods_desc	string	否	订单描述或备注信息
        签名	    sign	string	否	见签名算法
     *
     * 收到回调处理完业务之后请输出固定的 success
     */
    public function rechargeCallback($request)
    {
        // 验证参数
        if ($request->shop_id <> self::$merchantID
            || $request->api_name <> 'quickpay.all.native.callback'
            || $request->pay_result <> 'success'
        ) {
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

        if ($rechargeLog->status == 2) {
            $this->_msg = '已成功充值,无需再回调';
            return false;
        }

        DB::beginTransaction();
        try {
            $user = $this->userRepository->findByIdUser($rechargeLog->user_id);

            // 是否第一次充值
            if ((int)$user->is_first_recharge == 0 && $money >= 200) {
//                $user->is_first_recharge = 1;
                $referrensUser = $this->userRepository->findByIdUser($user->two_recommend_id);  // 给推荐我注册的人，推荐充值数加1
                if ($referrensUser) {
                    $referrensUser->rec_ok_count += 1;
                    $referrensUser->save();
                }
            }

            // 记录充值成功余额变动
            $this->userRepository->updateRechargeBalance($user, $money);

            // 更新充值成功记录的状态
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

    /**
     * 充值记录
     */
    public function rechargeLog($request)
    {
        return $this->rechargeRepository->getRechargeLogs($request->status, $request->limit, $request->page);
    }
}

