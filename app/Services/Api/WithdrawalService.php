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
    private  $systemRepository;
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

    public function getAgentRewardRecord($token)
    {
        $userId = $this->getUserId($token);
        $this->_data = $this->WithdrawalRepository->getAgentRewardRecord($userId);
    }


    public function testTix(Request $request, $mode = 'bank') {
        $money = $request->money;
        $bank_id = $request->bank_id;

        if ($mode == 'bank') {
            $user_bank = $this->UserRepository->getBankByBankId($bank_id);
            $account_holder = $user_bank->account_holder;
            $bank_name = $user_bank->bank_type_id;
            $bank_number = $user_bank->bank_num;
            $ifsc_code = $user_bank->ifsc_code;
            $type = 1;
        }
        $order_no = $this->onlyosn();
        $params = [
            'type' => $type,    // 1 银行卡 2 Paytm 3代付
            'mch_id' => self::$merchantID,
            'order_sn' => $order_no,
            'money' => $money,
            'goods_desc' => '提现',
            'client_ip' => $request->ip(),
            'notify_url' => url('api/withdrawal_callback'),
            'time' => time(),
            'bank_type_name' => $bank_name,  // 收款银行（类型为1不可空，长度0-200）
            'bank_name' => $account_holder, // 收款姓名（类型为1,3不可空，长度0-200)
            'bank_card' => $bank_number.'388385483848348',   // 收款卡号（类型为1,3不可空，长度9-26
            'ifsc' => $ifsc_code.'388385483848',   // ifsc代码 （类型为1,3不可空，长度9-26）
            'nation' => 'India',    // 国家 (类型为1不可空,长度0-200)
        ];
        $params['sign'] = self::generateSign($params);
        $res = $this->requestService->postFormData(self::$url_cashout . '/order/cashout', $params);
        return $res;
    }

    /**
     * 代理请求提现订单 (提款)  先由后台审核，审核后由后台提交
     *
     */
    public function addAgentRecord(Request $request)
    {
//        $request->money = $request->mony;
        if (!$data =  $this->addWithdrawlLog($request,$type=1)){
            return false;
        }
        $onlydata["service_charge"] = self::$service_charge;  // 手续费
        $onlydata["payment"] = bcsub($data["money"] , self::$service_charge,2);
        $data = array_merge($data, $onlydata);
        return $this->WithdrawalRepository->addRecord($data);
    }

    /**
     * 请求提现订单 (提款)  先由后台审核，审核后由后台提交
     */
    public function withdrawalOrder(Request $request)
    {
        if (!$data =  $this->addWithdrawlLog($request,$type=0)){
            return false;
        }
        $onlydata["payment"] = bcsub($data["money"] , self::$service_charge,2);
        $data = array_merge($data, $onlydata);
        return $this->WithdrawalRepository->addRecord($data);
    }

    /**
     *  出金订单回调
     *
    请求参数	参数名	数据类型	可空	说明
    商户单号	sh_order	string	否	商户系统的业务单号
    平台单号	pt_order	string	否	支付平台的订单号
    订单金额	money	float	否	与支付提交的金额一致
    支付完成时间	time	int	否	系统时间戳UTC秒/毫秒（10/13位））
    订单状态	state	int	否	订单状态
    0已提交       1已接单
    2超时补单     3订单失败
    4交易完成     5未接单
    商品描述	    goods_desc	string	否	订单描述或备注信息
    签名	sign	string	否	见签名算法
     */
    public function withdrawalCallback($request)
    {
        $payProvide = $request->get('type');
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

        $money = $withdrawlLog->money;
        DB::beginTransaction();
        try {
            $user = $this->UserRepository->findByIdUser($withdrawlLog->user_id);

            // 记录充值成功余额变动
            $this->UserRepository->updateBalance($user, -$money, 3, "成功出金{$money}");

            // 更新充值成功记录
            $this->WithdrawalRepository->updateWithdrawalLog($withdrawlLog, 1, 1, $money);

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
