<?php


namespace App\Services\Api;


use App\Repositories\Api\SystemRepository;
use App\Repositories\Api\UserRepository;
use App\Repositories\Api\WithdrawalRepository;
use App\Services\BaseService;
use App\Services\Pay\PayContext;
use App\Services\Pay\PayStrategy;
use App\Services\PayService;
use App\Services\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalService extends PayService
{
    private $WithdrawalRepository, $UserRepository;
    private $systemRepository;
    private $requestService;
    private $payContext;

    public static $service_charge = 45;  // 手续费

    public function __construct(WithdrawalRepository $repository,
                                UserRepository $userRepository,
                                RequestService $requestService,
                                PayContext $payContext,
                                SystemRepository $systemRepository
    )
    {
        $this->WithdrawalRepository = $repository;
        $this->UserRepository = $userRepository;
        $this->requestService = $requestService;
        $this->systemRepository = $systemRepository;

        $this->payContext = $payContext;
    }

    public function getRecords($token)
    {
        $userId = $this->getUserId($token);
        $this->_data = $this->WithdrawalRepository->findRecordByUserId($userId);
    }

    public function addRecord($data, $token)
    {
        $userId = $this->getUserId($token);
        $data["user_id"] = $userId;
        $data["create_time"] = time();
        if ($this->WithdrawalRepository->addRecord($data)) {
            $this->_msg = "提现申请成功";
        } else {
            $this->_code = 402;
            $this->_msg = "提现申请失败";
        }
    }

    public function getMessage($id)
    {
        $this->_data = $this->WithdrawalRepository->getMessage($id);
    }

    public function getAgentWithdrawalRecord($token)
    {
        $userId = $this->getUserId($token);
        $this->_data = $this->WithdrawalRepository->getAgentWithdrawalRecord($userId);
    }

    public function getAgentRewardRecord($token, $type)
    {
        $userId = $this->getUserId($token);
        $this->_data = $this->WithdrawalRepository->getAgentRewardRecord($userId, $type);
    }


    /**
     * 代理请求提现订单 (提款佣金)  先由后台审核，审核后由后台提交
     */
    public function addAgentRecord(Request $request)
    {
        if (!$data = $this->addWithdrawlLog($request, $type = 1)) {
            return false;
        }
        $onlydata["service_charge"] = self::$service_charge;  // 手续费
        $onlydata["payment"] = bcsub($data["money"], self::$service_charge, 2);
        $data = array_merge($data, $onlydata);
        $this->WithdrawalRepository->addRecord($data);

      $user = $this->UserRepository->findByIdUser($data['user_id']);
      return [
          'balance' => $user->balance,
          'commission' => $user->commission,
      ];
    }

    /**
     * 用户请求提现订单 (提款余额)  先由后台审核，审核后由后台提交
     */
    public function withdrawalOrder(Request $request)
    {

        if (!$data = $this->addWithdrawlLog($request, $type = 0)) {
            return false;
        }
        $onlydata["payment"] = bcsub($data["money"], self::$service_charge, 2);
        $data = array_merge($data, $onlydata);
          $this->WithdrawalRepository->addRecord($data);

        $user = $this->UserRepository->findByIdUser($data['user_id']);
        return [
            'balance' => $user->balance,
            'commission' => $user->commission,
        ];
    }

    /**
     * 用户和代理提现公共方法
     * 'type' => 1,  // 类型，0:用户提现 1:代理佣金提现
     */
    private function addWithdrawlLog(Request $request, $type = 1)
    {
        $user_id = $this->getUserId($request->header("token"));
        $user = $this->UserRepository->findByIdUser($user_id);

        $bank_id = $request->bank_id;
        $money = $request->money;

        $user_bank = $this->UserRepository->getBankByBankId($bank_id);
        if ($user_bank->user_id <> $user_id) {
            $this->_msg = 'The bank card does not match';
            return false;
        }

        // 0:用户提现 余额提现
        if ($type == 0) {

            if ((float)$user->balance < $money) {
                $this->_msg = 'The withdrawal amount is greater than the balance';
                return false;
            }

            $system = $this->systemRepository->getSystem();
            if ((int)$system->multiple > 0) {
                if (((float)$user->total_recharge * (int)$system->multiple) < $money) {
                    $this->_msg = "Your order amount is not enough to complete the withdrawal of {$money} amount, please complete the corresponding order amount before initiating the withdrawal";
                    return false;
                }
            }
            if (((float)$user->cl_betting - $user->cl_withdrawal) < $money * (int)$system->multiple) {
                $this->_msg = "Your order amount is not enough to complete the withdrawal of {$money} amount, please complete the corresponding order amount before initiating the withdrawal";
                return false;
            }
            $user->balance = bcsub($user->balance,$money,2);
            $user->freeze_money = bcadd($user->freeze_money,$money,2);
            $user->save();
        } elseif ($type == 1) {

            if ((float)$user->commission < $money) {
                $this->_msg = 'The withdrawal amount is greater than the balance';
                return false;
            }

            //  0:代理提现  佣金提现
            $user->commission= bcsub($user->commission,$money,2);
            $user->freeze_agent_money= bcadd($user->freeze_agent_money,$money,2);
            $user->save();
        }
        $account_holder = $user_bank->account_holder;
        $bank_name = $user_bank->bank_type_id;
        $bank_number = $user_bank->bank_num;
        $ifsc_code = $user_bank->ifsc_code;
        $phone = $user_bank->phone;
        $email = $user_bank->mail;
        $order_no = PayStrategy::onlyosn();
        $data = [
            'user_id' => $user_id,
            'phone' => $phone,
            'nickname' => $user->nickname,
            'money' => $money,
            'create_time' => time(),
            'order_no' => $order_no,
            'pltf_order_no' => '',
            'upi_id' => '',
            'account_holder' => $account_holder,
            'bank_number' => $bank_number,
            'bank_name' => $bank_name,
            'ifsc_code' => $ifsc_code,
            'pay_status' => 0,
            'type' => 0,
            'status' => 0,
            'email' => $email,
            'type' => $type,
        ];
        return $data;
    }

    /**
     *  出金订单回调
     *
     * 请求参数    参数名    数据类型    可空    说明
     * 商户单号    sh_order    string    否    商户系统的业务单号
     * 平台单号    pt_order    string    否    支付平台的订单号
     * 订单金额    money    float    否    与支付提交的金额一致
     * 支付完成时间    time    int    否    系统时间戳UTC秒/毫秒（10/13位））
     * 订单状态    state    int    否    订单状态
     * 0已提交       1已接单
     * 2超时补单     3订单失败
     * 4交易完成     5未接单
     * 商品描述        goods_desc    string    否    订单描述或备注信息
     * 签名    sign    string    否    见签名算法
     */
    public function withdrawalCallback(Request $request)
    {
        \Illuminate\Support\Facades\Log::channel('mytest')->info('withdrawalCallback',$request->all());
//        dd($request->post());
//        return false;
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
        if (!$where = $strategyClass->withdrawalCallback($request)) {
            $this->_msg = $strategyClass->_msg;
            return false;
        }

        $withdrawlLog = $this->WithdrawalRepository->getWithdrawalInfoByCondition($where);
        if (!$withdrawlLog) {
            $this->_msg = '找不到此出金订单';
            return false;
        }

        if ($withdrawlLog->pay_status == 1) {
            $this->_msg = '已成功提现,无需再回调';
            return false;
        }

        $money = $withdrawlLog->money;      // 申请金额
        $payment = $withdrawlLog->payment;  // 手续费之后的金额
        DB::beginTransaction();
        try {
            $user = $this->UserRepository->findByIdUser($withdrawlLog->user_id);

            // 普通用户
            if ($withdrawlLog->type == 0) {
                // 记录充值成功余额变动
                $dq_balance = bcadd($user->balance,$user->freeze_money,2);     // 当前余额 (总余额+冻结金额)
                $wc_balance = bcsub($dq_balance, $money, 2);                   // 变动后余额
                $this->UserRepository->addBalanceLog($user->id, $money, 3, "成功提现{$money};减少冻结金额{$money}",$dq_balance,$wc_balance);

                // 更新用户金额
                $user->freeze_money = bcsub($user->freeze_money, $money,2); // 减掉冻结资金
                $user->cl_withdrawal = bcadd($user->cl_withdrawal , $money,2); // 累计提现
                $user->save();

             // 代理用户
            } elseif ($withdrawlLog->type == 1) {
                // 记录充值成功余额变动
                $dq_commission = bcadd($user->commission,$user->freeze_agent_money,2);     // 当前余额 (总佣金余额+冻结佣金金额)
                $wc_commission = bcsub($dq_commission, $money, 2);                            // 变动后余额
                $order_no = $withdrawlLog->order_no;
                $this->UserRepository->addCommissionLogs($user, $money, $dq_commission, $wc_commission, $order_no);

                // 更新用户金额
                $user->freeze_agent_money = bcsub($user->freeze_agent_money, $money,2); // 减掉代理冻结代理资金
                $user->cl_commission = bcadd($user->cl_commission , $money,2);
                $user->save();
            }

            // 更新充值成功记录
//            $this->WithdrawalRepository->updateWithdrawalLog($withdrawlLog, 1, 1, $money);
            $withdrawlLog->pay_status = 1;
            $withdrawlLog->loan_time = time();
            $withdrawlLog->save();

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
     * 提现查询
     *
     * 商户可以主动查询出金订单状态
     * 建议商户在接收到异步通知后，主动查询一次订单状态和通知状态对比。不建议采用轮询方式过于频繁的执行查询请求
     */
    public function withdrawalQuery($order_no)
    {
        $params = [
            "mch_id" => self::$merchantID,
            "out_order_sn" => $order_no,
            "time" => time(),
        ];
        $params['sign'] = self::generateSign($params);
        return $this->requestService->postJsonData(self::$url . '/withdrawalQuery', $params);
    }
}
