<?php


namespace App\Repositories\Admin\agent;

use App\Models\Cx_Charge_Logs;
use App\Models\Cx_Sign_Order;
use App\Models\Cx_User_Balance_Logs;
use App\Models\Cx_User_Recharge_Logs;
use App\Models\Cx_Withdrawal_Record;

class AgentFinanceRepository
{

    protected $Cx_User_Recharge_Logs,
        $Cx_Withdrawal_Record,
        $Cx_Charge_Logs,
        $Cx_User_Balance_Logs,
        $Cx_Sign_Order;

    public function __construct
    (
        Cx_User_Recharge_Logs $cx_User_Recharge_Logs,
        Cx_Withdrawal_Record $cx_Withdrawal_Record,
        Cx_Charge_Logs $cx_Charge_Logs,
        Cx_User_Balance_Logs $cx_User_Balance_Logs,
        Cx_Sign_Order $cx_Sign_Order
    )
    {
        $this->Cx_User_Recharge_Logs = $cx_User_Recharge_Logs;
        $this->Cx_Withdrawal_Record = $cx_Withdrawal_Record;
        $this->Cx_Charge_Logs = $cx_Charge_Logs;
        $this->Cx_User_Balance_Logs = $cx_User_Balance_Logs;
        $this->Cx_Sign_Order = $cx_Sign_Order;
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
     * @param $user_ids
     * @param $size
     * @return mixed
     */
    public function commissionList($where, $user_ids, $size){
        return $this->Cx_Charge_Logs
            ->whereIntegerInRaw('charge_user_id',$user_ids)
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

    /**
     * 裂变红包任务
     * @param $where
     * @param $user_ids
     * @param $size
     * @return mixed
     */
    public function envelopeList($where, $user_ids, $size){
        return $this->Cx_User_Balance_Logs
            ->whereIntegerInRaw('user_id',$user_ids)
            ->where($where)
            ->with(
                [
                    'user' => function($query){
                        $query->select(['id', 'rec_ok_count', 'nickname', 'phone as phone_hide']);
                    }
                ]
            )
            ->select(['id', 'user_id', 'money', 'time'])
            ->orderByDesc('time')
            ->paginate($size);
    }

    /**
     * 签到红包
     * @param $where
     * @param $user_ids
     * @param $size
     * @return mixed
     */
    public function signInList($where, $user_ids, $size){
        return $this->Cx_Sign_Order
            ->whereIntegerInRaw('user_id',$user_ids)
            ->where($where)
            ->select(['id', 'phone as phone_hide', 'nickname', 'user_id', 'amount', 'daily_rebate', 'start_time', 'end_time', 'yet_receive_count', 'yet_receive_amount'])
            ->paginate($size);
    }

}
