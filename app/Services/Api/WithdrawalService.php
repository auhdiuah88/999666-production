<?php


namespace App\Services\Api;


use App\Repositories\Api\UserRepository;
use App\Repositories\Api\WithdrawalRepository;
use App\Services\BaseService;
use App\Services\PayService;
use App\Services\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalService extends PayService
{
    private $WithdrawalRepository, $UserRepository;
    private $requestService;

    public function __construct(WithdrawalRepository $repository,
                                UserRepository $userRepository,
                                RequestService $requestService
    )
    {
        $this->WithdrawalRepository = $repository;
        $this->UserRepository = $userRepository;
        $this->requestService = $requestService;
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

    public function addAgentRecord($data, $token)
    {
        $userId = $this->getUserId($token);
        $user = $this->UserRepository->findByIdUser($userId);
        $data["user_id"] = $userId;
        $data["create_time"] = time();
        $data["type"] = 1;
        $data["service_charge"] = 45;
        $data["order_no"] = time() . $userId;
        $data["phone"] = $user->phone;
        $data["nickname"] = $user->nickname;
        $data["payment"] = $data["money"] - 45;
        if ($this->WithdrawalRepository->addRecord($data)) {
            $this->_msg = "提现申请成功";
        } else {
            $this->_code = 402;
            $this->_msg = "提现申请失败";
        }
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
     * 请求出金订单 (提款)  先由后台审核，审核后由后台提交
     *
     * 商户可自助申请出金/代付
     *
     * UPI就是把之前转账时所需要填写的繁琐信息直接整合成一个字符串ID，不用再输入银行卡号等。这个UPI ID可以是一个人的名字，身份证号，手机号，邮箱，任意字符串等。
     */
    public function withdrawalOrder(Request $request, $mode = 'dai')
    {
        $user_id = $this->getUserId($request->header("token"));
        $user = $this->UserRepository->findByIdUser($user_id);

        $money = $request->money;
        $bank_id = $request->bank_id;

        $onlyParams = [];  // 各个支付独有的参数
        $upi_id = '';

        $user_bank = $this->UserRepository->getBankByBankId($bank_id);
        if ($user_bank->user_id <> $user_id) {
            $this->_msg = '银行卡不匹配';
            return false;
        }
        $account_holder = $user_bank->account_holder;
        $bank_name = $user_bank->bank_type_id;
        $bank_number = $user_bank->bank_num;
        $ifsc_code = $user_bank->ifsc_code;
        $phone = $user_bank->phone;
        $mail = $user_bank->mail;

        if ($mode == 'bank') {
            $type = 1;
            $onlyParams = [
                'bank_type_name' => $bank_name,  // 收款银行（类型为1不可空，长度0-200）
                'bank_name' => $account_holder, // 收款姓名（类型为1,3不可空，长度0-200)
                'bank_card' => $bank_number,   // 收款卡号（类型为1,3不可空，长度9-26
                'ifsc' => $ifsc_code,   // ifsc代码 （类型为1,3不可空，长度9-26）
                'nation' => 'India',    // 国家 (类型为1不可空,长度0-200)
            ];
        } else if ($mode == 'dai') {
            $type = 3;
            $onlyParams = [
                'bank_name' => $account_holder, // 收款姓名（类型为1,3不可空，长度0-200)
                'bank_card' => $bank_number,   // 收款卡号（类型为1,3不可空，长度9-26
                'ifsc' => $ifsc_code,   // ifsc代码 （类型为1,3不可空，长度9-26）
                'bank_tel' => $phone,   // 收款手机号（类型为3不可空，长度0-20）
                'bank_email' => $mail,   // 收款邮箱（类型为3不可空，长度0-100）
            ];
        } else if ($mode == 'upi') {
            $account_holder = 'xxxx';
            $bank_name = 'xxxx';
            $bank_number = 'xxxx';
            $ifsc_code = 'xxxx';
            $upi_id = $request->upi_id;
            $type = 2;
            $onlyParams = [
                'paytm_account', $upi_id   // Paytm账号 (类型为2不可空,长度0-200)
            ];
        }else {
            $this->_msg = '不支持的方式';
            return false;
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
        ];
        $params = array_merge($params, $onlyParams);
        $params['sign'] = self::generateSign($params);

        $res = $this->requestService->postFormData(self::$url_cashout . '/order/cashout', $params);
        if ($res['code'] <> 1) {
            $this->_msg = $res['msg'];
            return false;
        }
        $pltf_order_no = '';
        $this->WithdrawalRepository->addWithdrawalLog($user, $money, $order_no, $pltf_order_no,$upi_id,
            $account_holder, $bank_number, $bank_name, $ifsc_code, $params['sign']);
        return $res;
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

//        \Illuminate\Support\Facades\Log::channel('mytest')->info('withdrawalCallback', $request->all());

//        if ($request->rtn_code <> 'success') {
//            $this->_msg = '参数错误';
//            return false;
//        }

        if ($request->state <> 4) {
            $this->_msg = '交易未完成';
            return false;
        }

        // 验证签名
        $params = $request->post();
        $sign = $params['sign'];
        unset($params['sign']);
        if (PayService::generateSign($params) <> $sign) {
            $this->_msg = '签名错误';
            return false;
        }

//        $money = $request->money;
        $where = [
            'order_no' => $request->sh_order,
//            'pltf_order_no' => $request->pltf_order_id,
//            'money' => $money
        ];
        $withdrawlLog = $this->WithdrawalRepository->getWithdrawalInfoByCondition($where);
        if (!$withdrawlLog) {
            $this->_msg = '找不到此出金订单';
            return false;
        }

//        if ($withdrawlLog->status == 1) {
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
