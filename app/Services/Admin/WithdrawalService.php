<?php


namespace App\Services\Admin;


use App\Repositories\Admin\UserRepository;
use App\Repositories\Admin\WithdrawalRepository;
use App\Services\BaseService;

class WithdrawalService extends BaseService
{
    private $WithdrawalRepository, $UserRepository;

    public function __construct(WithdrawalRepository $withdrawalRepository, UserRepository $userRepository)
    {
        $this->WithdrawalRepository = $withdrawalRepository;
        $this->UserRepository = $userRepository;
    }

    public function findAll($page, $limit, $status)
    {
        $list = $this->WithdrawalRepository->findAll(($page - 1) * $limit, $limit, $status);
        $total = $this->WithdrawalRepository->countAll($status);
        $this->_data = ["total" => $total, "list" => $list];
    }

    /**
     * 审核
     */
    public function auditRecord($data)
    {
        if ($data["status"] == 1) {
            if ($data["type"] == 1) {
                $this->changeAgencyCommission($data["id"]);
                unset($data["type"]);
            } else {
                $this->addWithdrawalLogs($data["id"]);
            }
        }
        $data["loan_time"] = time();
        $data["approval_time"] = time();
        if ($this->WithdrawalRepository->editRecord($data)) {
            $this->_msg = "审核通过";
        } else {
            $this->_code = 402;
            $this->_msg = "审核失败";
        }
    }

    public function batchPassRecord($data)
    {
        $records = $this->WithdrawalRepository->findAllByIds($data["ids"]);
        foreach ($records as $record) {
            if ($record["type"] == 1) {
                $this->changeAgencyCommission($record["id"]);
            } else {
                $this->addWithdrawalLogs($record["id"]);
            }
        }
        if ($this->WithdrawalRepository->batchUpdateRecord($data["ids"], 1)) {
            $this->_msg = "审核成功";
        } else {
            $this->_code = 402;
            $this->_msg = "审核失败";
        }
    }

    public function batchFailureRecord($data)
    {
        if ($this->WithdrawalRepository->batchUpdateRecord($data["ids"], 2, $data["message"])) {
            $this->_msg = "审核成功";
        } else {
            $this->_code = 402;
            $this->_msg = "审核失败";
        }
    }

    public function changeAgencyCommission($id)
    {
        $record = $this->WithdrawalRepository->findById($id);
        $user = $this->UserRepository->findById($record->user_id);
        $userUpdate = ["id" => $user->id, "commission" => $user->commission - $record->payment, "cl_commission" => $user->cl_commission + $record->payment];
        $this->UserRepository->editUser($userUpdate);
        $data = [
            "user_id" => $user->id,
            "dq_commission" => $user->commission,
            "wc_commission" => $user->commission - $record->payment,
            "time" => time(),
            "order_no" => $record->order_no,
            "phone" => $user->phone,
            "nickname" => $user->nickname,
            "message" => $user->nickname . "提现佣金" . $record->payment . "成功！"
        ];
        $this->WithdrawalRepository->addCommissionLogs($data);
    }

    public function addWithdrawalLogs($id)
    {
        $record = $this->WithdrawalRepository->findById($id);
        $user = $this->UserRepository->findById($record->user_id);
        $userUpdate = ["id" => $user->id, "balance" => $user->balance - $record->payment, "cl_withdrawal" => $user->cl_withdrawal + $record->payment];
        $this->UserRepository->editUser($userUpdate);
        $insert = [
            "user_id" => $user->id,
            "type" => 3,
            "dq_balance" => $user->balance,
            "wc_balance" => $user->balance - $record->payment,
            "time" => time(),
            "msg" => $user->nickname . "提现" . $record->payment . "成功!"
        ];
        $this->WithdrawalRepository->addBalanceLogs($insert);
    }

    public function searchRecord($data)
    {
        $data = $this->getUserIds($data, "user_id");
        $list = $this->WithdrawalRepository->searchRecord($data, ($data["page"] - 1) * $data["limit"], $data["limit"]);
        $total = $this->WithdrawalRepository->countSearchRecord($data);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
