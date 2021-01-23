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
        if($status > -1){
            return $this->Cx_Withdrawal_Record
                ->with([
                    "user" => function ($query) {
                        $query->select(["id", "balance", "cl_withdrawal", "cl_commission", "total_recharge", "cl_betting", "cl_betting_total","phone", "remarks", "is_withdrawal", "phone"]);
                    },
                    "bank"
                ])
                ->where("status", $status)
                ->orderByDesc("create_time")
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->append(['pay_status_json'])
                ->toArray();
        }else{
            return $this->Cx_Withdrawal_Record
                ->with([
                    "user" => function ($query) {
                        $query->select(["id", "balance", "cl_withdrawal", "cl_commission", "total_recharge", "cl_betting", "cl_betting_total","phone", "remarks", "is_withdrawal", "phone"]);
                    },
                    "bank"
                ])
                ->orderByDesc("create_time")
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->append(['pay_status_json'])
                ->toArray();
        }

    }

    public function countAll($status)
    {
        if($status > -1){
            return $this->Cx_Withdrawal_Record->where("status", $status)->count("id");
        }else{
            return $this->Cx_Withdrawal_Record->count("id");
        }
    }

    public function findById($id)
    {
        return $this->Cx_Withdrawal_Record->where("id", $id)->first();
    }

    public function findByIds($ids)
    {
        return makeModel(['id'=>['in', $ids]], $this->Cx_Withdrawal_Record)->get()->toArray();
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
        return $this->Cx_Withdrawal_Record->whereIn("id", $ids)->select(["id", "type", "is_post"])->get()->toArray();
    }

    public function batchUpdateRecord($ids, $status, $message = null)
    {
        if ($message == null) {
            return $this->Cx_Withdrawal_Record->whereIn("id", $ids)->update(["status" => $status, "approval_time" => time(), 'is_post'=>0]);
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
        if(isset($data['conditions']['status']) && $data['conditions']['status'] == -1)unset($data['conditions']['status']);
        return $this->whereCondition($data, $this->Cx_Withdrawal_Record->with(["user" => function ($query) {
            $query->select(["id", "balance", "cl_withdrawal", "cl_commission", "total_recharge", "cl_betting", "cl_betting_total", "is_withdrawal", "remarks", "phone"]);
        }, "bank"]))->orderByDesc("create_time")->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countSearchRecord($data)
    {
        if(isset($data['conditions']['status']) && $data['conditions']['status'] == -1)unset($data['conditions']['status']);
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

    /**
     * 获取MTB申请提现已审核未支付的订单，查看支付状态
     * @return mixed
     */
    public function getMTBPayWaitCallList(){
        $time = time() - 15 * 60;
        return $this->Cx_Withdrawal_Record->where([["withdraw_type", "=", "MTBpay"], ["status", "=", 1], ["pay_status", "=", 0], ["call_count", "<", 4], ["call_time", "<", $time]])->orderByDesc('approval_time')->limit(5)->get();
    }

    public function callMTBFail($item){
        return $this->Cx_Withdrawal_Record->where("id", "=", $item->id)->update(["call_count"=>$item->call_count+1, "call_time"=>$item->time()]);
    }

    public function callMTBSuccess($item){
        return $this->Cx_Withdrawal_Record->where("id", "=", $item->id)->update(["call_count"=>$item->call_count+1, "call_time"=>$item->time(), "pay_status"=>1]);
    }

    public function callMTBDefeat($item){
        return $this->Cx_Withdrawal_Record->where("id", "=", $item->id)->update(["call_count"=>$item->call_count+1, "call_time"=>$item->time(), "pay_status"=>3]);
    }
}
