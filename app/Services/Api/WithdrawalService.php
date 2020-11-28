<?php


namespace App\Services\Api;


use App\Repositories\Api\UserRepository;
use App\Repositories\Api\WithdrawalRepository;
use App\Services\BaseService;
use App\Services\PayService;
use App\Services\RequestService;

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
    public function withdrawalOrder($request, $money, $upi_id, $account_holder, $bank_number, $bank_name, $ifsc_code)
    {
        $user_id = $this->getUserId($request->header("token"));
        $user = $this->UserRepository->findByIdUser($user_id);

        $order_no = $this->onlyosn();
        $params = [
            'shop_id' => self::$merchantID,
            'out_trade_no' => $order_no,
            'money' => $request->money,
            'upi_id' => $request->upi_id, // UPI帐号。1、UPI方式收款，该字段填写真实信息。account_holder、bank_number、bank_name、ifsc_code 这四个字段填"xxxx"。
            'account_holder' => $request->account_holder, // 银行账户人实名。2、银行卡方式收款，该字段填写真实信息。upi_id字段填"xxxx"。
            'bank_number' => $request->bank_number, // 银行卡号。2、银行卡方式收款，该字段填写真实信息。upi_id字段填"xxxx"。
            'bank_name' => $request->bank_name, // 银行名称。2、银行卡方式收款，该字段填写真实信息。upi_id字段填"xxxx"。
            'ifsc_code' => $request->ifsc_code, // IFSC编号。2、银行卡方式收款，该字段填写真实信息。upi_id字段填"xxxx"。
            'notify_url' => url('api/withdrawal_callback'), // 回调url，用来接收订单支付结果
        ];
        $params['sign'] = self::generateSign($params);
// 示例
//"account_holder": "Adarsh",
//"bank_name": "CanaraBank",
//"bank_number": "8888808000756",
//"ifsc_code": "CNRB0003745",
//"money": "475",
//"notify_url": "http://www.baidu.com",
//"out_trade_no": "8O2010291150433851",
//"shop_id": "10120",
//"upi_id": "88888888",
//"sign": "941012af1ce5cd5261024b719f6b22ab"

        $res = $this->requestService->postJsonData(self::$url . '/withdrawal', $params);
        if ($res['rtn_code'] <> 1000) {
            $this->_msg = $res['rtn_msg'];
            return false;
        }
        $this->WithdrawalRepository->addWithdrawalLog($user, $money, $order_no, $res['pltf_order_no'], $params['upi_id'],
            $params['account_holder'],$params['bank_number'],$params['bank_name'],$params['ifsc_code'], $params['sign']);
        return $res;
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
            "shop_id" =>  self::$merchantID
        ];
        return $this->requestService->postJsonData(self::$url . '/withdrawalQuery', $params);
    }
}
