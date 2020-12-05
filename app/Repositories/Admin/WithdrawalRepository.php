<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User_Balance_Logs;
use App\Models\Cx_User_Commission_Logs;
use App\Models\Cx_Withdrawal_Record;
use App\Repositories\BaseRepository;

class WithdrawalRepository extends BaseRepository
{
    private $Cx_Withdrawal_Record, $Cx_User_Balance_Logs, $Cx_User_Commission_Logs;

    public function __construct(
        Cx_Withdrawal_Record $cx_Withdrawal_Record,
        Cx_User_Balance_Logs $balance_Logs,
        Cx_User_Commission_Logs $commission_Logs
    )
    {
        $this->Cx_Withdrawal_Record = $cx_Withdrawal_Record;
        $this->Cx_User_Balance_Logs = $balance_Logs;
        $this->Cx_User_Commission_Logs = $commission_Logs;
    }

    public function findAll($offset, $limit, $status)
    {
        return $this->Cx_Withdrawal_Record->with(["user" => function ($query) {
            $query->select(["id", "balance", "cl_withdrawal", "cl_commission", "total_recharge", "cl_betting", "cl_betting_total"]);
        }, "bank"])->where("status", $status)->orderByDesc("create_time")->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countAll($status)
    {
        return $this->Cx_Withdrawal_Record->where("status", $status)->count("id");
    }

    public function findById($id)
    {
        return $this->Cx_Withdrawal_Record->where("id", $id)->first();
    }

    public function addBalanceLogs($data)
    {
        return $this->Cx_User_Balance_Logs->insertGetId($data);
    }

    public function addCommissionLogs($data)
    {
        return $this->Cx_User_Commission_Logs->insertGetId($data);
    }

    public function findAllByIds($ids)
    {
        return $this->Cx_Withdrawal_Record->whereIn("id", $ids)->select(["id", "type"])->get()->toArray();
    }

    public function batchUpdateRecord($ids, $status, $message = null)
    {
        if ($message == null) {
            return $this->Cx_Withdrawal_Record->whereIn("id", $ids)->update(["status" => $status, "approval_time" => time()]);
        } else {
            return $this->Cx_Withdrawal_Record->whereIn("id", $ids)->update(["status" => $status, "approval_time" => time(), "message" => $message]);
        }
    }

    public function editRecord($data)
    {
        return $this->Cx_Withdrawal_Record->where("id", $data["id"])->update($data);
    }

    public function searchRecord($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->Cx_Withdrawal_Record->with(["user" => function ($query) {
            $query->select(["id", "balance", "cl_withdrawal", "cl_commission", "total_recharge", "cl_betting", "cl_betting_total"]);
        }, "bank"]))->orderByDesc("create_time")->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countSearchRecord($data)
    {
        return $this->whereCondition($data, $this->Cx_Withdrawal_Record)->count("id");
    }

    /**
     * 获取最新一条提现数据
     */
    public function getNewest()
    {
        return $this->Cx_Withdrawal_Record->where('status', 0)->orderByDesc("id")->first(['create_time', 'id']);
    }

    /**
     * 获取最新一条提现数据
     */
    public function getNewests()
    {
        $limit = 10;
        $status = 0;
        return $this->Cx_Withdrawal_Record->with(["user" => function ($query) {
            $query->select(["id", "balance", "cl_withdrawal", "cl_commission", "total_recharge", "cl_betting", "cl_betting_total"]);
        }, "bank"])->where("status", $status)->orderByDesc("create_time")->limit($limit)->get();

//        return $this->Cx_Withdrawal_Record->where('status', 0)->orderByDesc("id")->limit(10)->get();
    }
}
