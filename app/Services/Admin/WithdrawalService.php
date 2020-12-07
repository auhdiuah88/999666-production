<?php


namespace App\Services\Admin;


use App\Repositories\Admin\UserRepository;
use App\Repositories\Admin\WithdrawalRepository;
use App\Services\BaseService;
use App\Services\Pay\PayContext;

class WithdrawalService extends BaseService
{
    private $WithdrawalRepository, $UserRepository;
    private $payContext;

    public function __construct(WithdrawalRepository $withdrawalRepository,
                                UserRepository $userRepository,
                                PayContext $payContext
    )
    {
        $this->WithdrawalRepository = $withdrawalRepository;
        $this->UserRepository = $userRepository;
        $this->payContext = $payContext;

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
    public function auditRecord($request)
    {
        $data = $request->post();
        $withdrawalRecord = $this->WithdrawalRepository->findById($data["id"]);
        if ($data["status"] == 1) {
//            if ($data["type"] == 1) {
//                $this->changeAgencyCommission($data["id"]);
//                unset($data["type"]);
//            }
//            else {
//                $this->addWithdrawalLogs($data["id"]);
//            }

            $host = $request->getHost();    // 根据api接口host判断是来源于哪个客户；用什么支付方式 //  $host = "api.999666.in"; 变成 999666.in
            if (count(explode('.', $host)) == 3) {
                $host = substr(strstr($host, '.'), 1);
            }
            if (!isset(PayContext::$pay_provider[$host])) {
                $this->_msg = 'not find strategy';
                return false;
            }
            $payProvide = PayContext::$pay_provider[$host];
            $strategyClass = $this->payContext->getStrategy($payProvide);  // 获取支付公司类
            $result = $strategyClass->withdrawalOrder($withdrawalRecord);
            if (!$result) {
                $this->_code = 414;
                $this->_msg = $strategyClass->_msg;
                return false;
            }
            $data['pltf_order_no'] = $request['pltf_order_no'];
//            $data['order_no'] = $request['order_no'];
        } elseif ($data["status"] == 2) {  // 如果审核不通过，将冻结金额返还
            $user = $this->UserRepository->findById($withdrawalRecord->user_id);
            $type = $withdrawalRecord->type;  //  0:用户提现 1:代理佣金提现
            if ($data["type"] == 1) {
                $user->freeze_agent_money = bcsub($user->freeze_agent_money, $withdrawalRecord->money,2);
                $user->commission = bcadd($user->commission, $withdrawalRecord->money,2);
            } else {
                $user->freeze_money = bcsub($user->freeze_money, $withdrawalRecord->money,2);
                $user->balance = bcadd($user->balance, $withdrawalRecord->money,2);
            }
            $user->save();
        }
//        $data["loan_time"] = time();
        $data["approval_time"] = time();
        if ($this->WithdrawalRepository->editRecord($data)) {
            $this->_msg = "审核通过";
            return true;
        } else {
            $this->_code = 402;
            $this->_msg = "审核失败";
            return false;
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

    /**
     * 获取最新的一条待审核的提现
     */
    public function getNewest()
    {
        $result =  $this->WithdrawalRepository->getNewest();
        if (!$result){
            return ['create_time'=>0, 'id'=>0];
        }
        return $result;
    }

    /**
     * 获取最新的10条待审核的提现
     */
    public function getNewests()
    {
        return $this->WithdrawalRepository->getNewests();
    }
}
