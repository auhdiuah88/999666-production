<?php


namespace App\Repositories\Admin\agent;

use App\Models\Cx_Charge_Logs;
use App\Models\Cx_User_Recharge_Logs;
use App\Models\Cx_Withdrawal_Record;

class AgentFinanceRepository
{

    protected $Cx_User_Recharge_Logs, $Cx_Withdrawal_Record, $Cx_Charge_Logs;

    public function __construct(Cx_User_Recharge_Logs $cx_User_Recharge_Logs, Cx_Withdrawal_Record $cx_Withdrawal_Record, Cx_Charge_Logs $cx_Charge_Logs){
        $this->Cx_User_Recharge_Logs = $cx_User_Recharge_Logs;
        $this->Cx_Withdrawal_Record = $cx_Withdrawal_Record;
        $this->Cx_Charge_Logs = $cx_Charge_Logs;
    }

    /**
     * 充值列表
     * @param $where
     * @param $user_ids
     * @param $size
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function rechargeList($where, $user_ids, $size){
        return $this->Cx_User_Recharge_Logs
            ->with(
                [
                    'user' => function($query){
                        $query->select(['id', 'phone as phone_hide', 'nickname']);
                    }
                ]
            )
            ->select(["*", "phone as phone_hide"])
            ->whereIntegerInRaw('user_id',$user_ids)
            ->where($where)
            ->orderByDesc('time')
            ->paginate($size);
    }

    /**
     * 提现列表
     * @param $where
     * @param $user_ids
     * @param $size
     * @return mixed
     */
    public function withdrawList($where, $user_ids, $size){
        return $this->Cx_Withdrawal_Record
            ->whereIntegerInRaw('user_id',$user_ids)
            ->where($where)
            ->with(
                [
                    'user' => function($query){
                        $query->select(['id', 'nickname', 'total_recharge', 'cl_withdrawal', 'commission', 'cl_betting', 'cl_betting_total']);
                    }
                ]
            )
            ->orderByDesc('create_time')
            ->paginate($size);
    }

    /**
     * 分佣列表
     * @param $where
     * @param $size
     * @return mixed
     */
    public function commissionList($where, $size){
        return $this->Cx_Charge_Logs
            ->where($where)
            ->with(
                [
                    'charge_user' => function($query){
                        $query->select(['id', 'phone as phone_hide', 'nickname']);
                    }
                ]
            )
            ->orderByDesc('create_time')
            ->paginate($size);
    }

}
