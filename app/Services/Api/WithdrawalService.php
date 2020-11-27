<?php


namespace App\Services\Api;


use App\Repositories\Api\UserRepository;
use App\Repositories\Api\WithdrawalRepository;
use App\Services\BaseService;

class WithdrawalService extends BaseService
{
    private $WithdrawalRepository, $UserRepository;

    public function __construct(WithdrawalRepository $repository, UserRepository $userRepository)
    {
        $this->WithdrawalRepository = $repository;
        $this->UserRepository = $userRepository;
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
}
