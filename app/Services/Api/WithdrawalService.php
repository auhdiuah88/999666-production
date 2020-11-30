<?php


namespace App\Services\Api;


use App\Repositories\Api\UserRepository;
use App\Repositories\Api\WithdrawalRepository;
use App\Services\BaseService;
use App\Services\PayService;
use App\Services\RequestService;
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


    /**
     * 请求出金订单 (提款)
     *
     * 商户可自助申请出金/代付
     *
     * UPI就是把之前转账时所需要填写的繁琐信息直接整合成一个字符串ID，不用再输入银行卡号等。这个UPI ID可以是一个人的名字，身份证号，手机号，邮箱，任意字符串等。
     */
    public function withdrawalOrder($request, $mode = 'bank')
    {
        $user_id = $this->getUserId($request->header("token"));
        $user = $this->UserRepository->findByIdUser($user_id);

        $money = $request->money;
        $bank_id = $request->bank_id;

        if ($mode == 'bank') {
            $user_bank = $this->UserRepository->getBankByBankId($bank_id);
            if ($user_bank->user_id <> $user_id) {
                $this->_msg = '银行卡不匹配';
                return false;
            }
            $account_holder = $user_bank->account_holder;
            $bank_name = $user_bank->bank_type_id;
            $bank_number = $user_bank->bank_num;
            $ifsc_code = $user_bank->ifsc_code;
            $upi_id = 'xxxx';
        } else if ($mode == 'upi') {
            $account_holder = 'xxxx';
            $bank_name = 'xxxx';
            $bank_number = 'xxxx';
            $ifsc_code = 'xxxx';
            $upi_id = $request->upi_id;
        } else {
            $this->_msg = '不支持的方式';
            return false;
        }
        $order_no = $this->onlyosn();
        $params = [
            'account_holder' => $account_holder, // 银行账户人实名。2、银行卡方式收款，该字段填写真实信息。upi_id字段填"xxxx"。
            'bank_name' => $bank_name, // 银行名称。2、银行卡方式收款，该字段填写真实信息。upi_id字段填"xxxx"。
            'bank_number' => $bank_number, // 银行卡号。2、银行卡方式收款，该字段填写真实信息。upi_id字段填"xxxx"。
            'ifsc_code' => $ifsc_code, // IFSC编号。2、银行卡方式收款，该字段填写真实信息。upi_id字段填"xxxx"。
            'money' => $money,
            'notify_url' => url('api/withdrawal_callback'), // 回调url，用来接收订单支付结果
            'out_trade_no' => $order_no,
            'shop_id' => self::$merchantID,
            'upi_id' => $upi_id, // UPI帐号。1、UPI方式收款，该字段填写真实信息。account_holder、bank_number、bank_name、ifsc_code 这四个字段填"xxxx"。
        ];
        $params['sign'] = self::generateSign($params);
        $res = $this->requestService->postJsonData(self::$url . '/withdrawal', $params);
        if ($res['rtn_code'] <> 1000) {
            $this->_msg = $res['rtn_msg'];
            return false;
        }
        $this->WithdrawalRepository->addWithdrawalLog($user, $money, $order_no, $res['pltf_order_no'], $params['upi_id'],
            $params['account_holder'], $params['bank_number'], $params['bank_name'], $params['ifsc_code'], $params['sign']);
        return $res;
    }

    /**
     *  出金订单回调
     */
    public function withdrawalCallback($request)
    {
        /**
         * "money": "2000",
         * "out_trade_no": "1912968483419341DA",
         * "pltf_order_id": "17800000000000297866",
         * "rtn_code": "success",
         * "sign": "f6c45be47606e0d84b20dbfb42b64e82"
         */

        /**
         * {
         * "money": "54.36",
         * "out_trade_no": "202011281743443450333436",
         * "pltf_order_id": "2559202011281743444014",
         * "rtn_code": "success",
         * "sign": "2463f17f8400c0416d0dd86c28208508"
         * }
         */

        if ($request->rtn_code <> 'success') {
            $this->_msg = '参数错误';
            return false;
        }

        // 验证签名
//        $params = $request->post();
//        $sign = $params['sign'];
//        unset($params['sign']);
//        if (PayService::generateSign($params) <> $sign){
//            $this->_msg = '签名错误';
//            return false;
//        }

//        $money = $request->money;
        $where = [
            'order_no' => $request->out_trade_no,
            'pltf_order_no' => $request->pltf_order_id,
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
     * 出金订单查询
     *
     * 商户可以主动查询出金订单状态
     * 建议商户在接收到异步通知后，主动查询一次订单状态和通知状态对比。不建议采用轮询方式过于频繁的执行查询请求
     */
    public function withdrawalQuery($order_no)
    {
        $params = [
            "out_trade_no" => $order_no,
            "shop_id" => self::$merchantID
        ];
        return $this->requestService->postJsonData(self::$url . '/withdrawalQuery', $params);
    }
}
