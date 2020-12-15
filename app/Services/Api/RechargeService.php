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
use App\Services\Pay\PayContext;
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
    private $payContext;

    public function __construct(UserRepository $userRepository,
                                RechargeRepository $rechargeRepository,
                                WithdrawalRepository $withdrawalRepository,
                                RequestService $requestService,
                                PayContext $payContext
    )
    {
        $this->userRepository = $userRepository;
        $this->rechargeRepository = $rechargeRepository;
        $this->withdrawalRepository = $withdrawalRepository;
        $this->requestService = $requestService;
        $this->payContext = $payContext;
    }

    /***
     * 充值下单接口-JSON封装请求
     * GET方式  返回支付URL链接
     */
    public function rechargeOrder(Request $request)
    {
        $user_id = $this->getUserId($request->header("token"));
        $user = $this->userRepository->findByIdUser($user_id);

        $pay_type = $request->pay_type;
        $money = $request->money;

        $host = $request->getHost();    // 根据api接口host判断是来源于哪个客户；用什么支付方式  //  $host = "api.999666.in"; 变成 999666.in
        if (count(explode('.', $host)) == 3) {
            $host = substr(strstr($host, '.'), 1);
        }

//        $payProvide = PayContext::$pay_provider[$host];
        $payProvide = $pay_type;   // 根据传入的类型（支付公司类型选折策略）
        $strategyClass = $this->payContext->getStrategy($payProvide);  // 获取支付商户类
        if (!$strategyClass) {
            $this->_msg = 'can not find pay mode';
            return false;
        }
        $result = $strategyClass->rechargeOrder($pay_type, $money);
        if (!$result) {
            $this->_msg = $strategyClass->_msg;
            return false;
        }
        // 添加充值记录
        $order_no = $result['out_trade_no'];
        $pay_type = $result['pay_type'];
        $pltf_order_id = $result['pltf_order_id'];
        $native_url = $result['native_url'];
        $verify_money = $result['verify_money'];
        $match_code = $result['match_code'];
        $result['is_post'] = isset($result['is_post'])?$result['is_post']:0;
        $this->rechargeRepository->addRechargeLog($user, $money, $order_no, $pay_type, $pltf_order_id, $native_url, $verify_money, $match_code);
        return $result;
    }

    /**
     * 充值订单查询
     */
//    public function orderQuery($order_no, $pltf_order_id = '')
//    {
//        $params = [
//            'mch_id' => self::$merchantID,
//            'out_order_sn' => $order_no,
//            'time' => time(),
//        ];
//        $params['sign'] = self::generateSign($params);
//        return $this->requestService->postFormData(self::$url . '/order/query', $params);
//    }

    /**
     *  充值回调
     *
     * 请求参数    参数名    数据类型    可空    说明
     * 商户单号        sh_order    string    否    商户系统的业务单号
     * 平台单号        pt_order    string    否    支付平台的订单号
     * 订单金额        money    float    否    与支付提交的金额一致
     * 支付完成时间    time    int    否    系统时间戳UTC秒（10位）
     * 订单状态        state    int    否    订单状态
     * 0 已提交       1 已接单
     * 2 超时补单     3 订单失败
     * 4 交易完成     5 未接单
     * 商品描述    goods_desc    string    否    订单描述或备注信息
     * 签名        sign    string    否    见签名算法
     *
     * 收到回调处理完业务之后请输出固定的 success
     */
    public function rechargeCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('rechargeCallback',$request->all());

        $payProvide = $request->get('type','');
        if (!$payProvide) {
            $this->_msg = 'can not find pay Provide';
            return false;
        }
        $strategyClass = $this->payContext->getStrategy($payProvide);  // 获取支付提供商类
        if (!$strategyClass) {
            $this->_msg = 'can not find pay mode';
            return false;
        }
        if (!$where = $strategyClass->rechargeCallback($request)) {
            $this->_msg = $strategyClass->_msg;
            return false;
        }

        $money = isset($request->money)?$request->money : $request->amount;
        // 下面的方法相同
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
            $rechargeLog->arrive_time = time();
            $rechargeLog->arrive_money = $money;
            $rechargeLog->status = 2;
            $rechargeLog->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
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

