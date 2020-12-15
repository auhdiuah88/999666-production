<?php


namespace App\Repositories\Admin\agent;


use App\Models\Cx_Charge_Logs;
use App\Models\Cx_Game_Betting;
use App\Models\Cx_Sign_Order;
use App\Models\Cx_User;
use App\Models\Cx_User_Balance_Logs;
use App\Models\Cx_User_Commission_Logs;
use App\Models\Cx_User_Recharge_Logs;
use App\Models\Cx_Withdrawal_Record;

class AgentDataRepository
{

    protected $Cx_User, $Cx_Game_Betting, $Cx_User_Recharge_logs, $Cx_Withdrawal_Record, $Cx_Charge_logs, $Cx_Sign_Order, $Cx_User_Balance_Logs;

    public $user_id;

    public $user_ids;

    public $time_map;

    public function __construct(Cx_User $cx_User, Cx_Game_Betting $cx_Game_Betting, Cx_User_Recharge_Logs $cx_User_Recharge_Logs, Cx_Withdrawal_Record $cx_Withdrawal_Record, Cx_Charge_Logs $cx_Charge_Logs, Cx_Sign_Order $cx_Sign_Order, Cx_User_Balance_Logs $cx_User_Balance_Logs){
        $this->Cx_User = $cx_User;
        $this->Cx_Game_Betting = $cx_Game_Betting;
        $this->Cx_User_Recharge_logs = $cx_User_Recharge_Logs;
        $this->Cx_Withdrawal_Record = $cx_Withdrawal_Record;
        $this->Cx_Charge_logs = $cx_Charge_Logs;
        $this->Cx_Sign_Order = $cx_Sign_Order;
        $this->Cx_User_Balance_Logs = $cx_User_Balance_Logs;
    }

    public function getMemberTotal(){
        return count($this->user_ids);
    }

    public function getNewMemberNum(){
        $where = [
            ['id', 'in', $this->user_ids]
        ];
        if($this->time_map)
            $where[] = ['reg_time', 'BETWEEN', $this->time_map];
        return $this->Cx_User->where($where)->count();
    }

    public function getActiveMemberNum(){
        $where = [
            ['user_id', 'in', $this->user_ids]
        ];
        if($this->time_map)
            $where[] = ['betting_time', 'BETWEEN', $this->time_map];
        return $this->Cx_Game_Betting->where($where)->groupBy('user_id')->count();
    }

    public function getUserIds(){
        return $this->Cx_User->where('invite_relation', 'like', '%-'. $this->user_id .'-%')->pluck('id');
    }

    public function getFirstRechargeNum(){
        $where = [
            ['user_id', 'in', $this->user_ids],
            ['is_first_recharge', '=', 1],
            ['status', '=', 2]
        ];
        if($this->time_map)
            $where[] = ['time', 'BETWEEN', $this->time_map];
        return $this->Cx_User_Recharge_logs->where($where)->count();
    }

    public function getRechargeMoney(){
        $where = [
            ['user_id', 'in', $this->user_ids],
            ['status', '=', 2]
        ];
        if($this->time_map)
            $where[] = ['time', 'BETWEEN', $this->time_map];
        return $this->Cx_User_Recharge_logs->where($where)->sum('money');
    }

    public function getSuccessWithDrawMoney(){
        return $this->getWithdrawMoney(1);
    }

    public function getWaitWithdrawMoney(){
        return $this->getWithdrawMoney(0);
    }

    public function getBalanceCommission(){
        return bcadd($this->getBalance(), $this->getCommission(),2);
    }

    public function getCommissionMoney(){
        $where = [
            ['charge_user_id', 'in', $this->user_ids]
        ];
        if($this->time_map)
            $where[] = ['create_time', 'BETWEEN', $this->time_map];
        return $this->Cx_Charge_logs->where($where)->sum('money');
    }

    public function getSignMoney(){
        $where = [
            ['user_id', 'in', $this->user_ids],
        ];
        if($this->time_map)
            $where[] = ['start_time', 'BETWEEN', $this->time_map];
        return $this->Cx_Sign_Order->where($where)->sum('amount');
    }

    public function getReceiveSIgnMoney(){
        $where = [
            ['user_id', 'in', $this->user_ids],
        ];
        if($this->time_map)
            $where[] = ['start_time', 'BETWEEN', $this->time_map];
        return $this->Cx_Sign_Order->where($where)->sum("yet_receive_count");
    }

    public function getGiveMoney(){
        $where = [
            ['user_id', 'in', $this->user_ids],
            ['type', '=', 5]
        ];
        if($this->time_map)
            $where[] = ['time', 'BETWEEN', $this->time_map];
        return $this->Cx_User_Balance_Logs->where($where)->sum('money');
    }

    public function getOrderNum(){
        $where = [
            ['user_id', 'in', $this->user_ids]
        ];
        if($this->time_map)
            $where[] = ['betting_time', 'BETWEEN', $this->time_map];
        return $this->Cx_Game_Betting->where($where)->count();
    }

    public function getOrderMoney(){
        $where = [
            ['user_id', 'in', $this->user_ids]
        ];
        if($this->time_map)
            $where[] = ['betting_time', 'BETWEEN', $this->time_map];
        return $this->Cx_Game_Betting->where($where)->sum('money');
    }

    public function getOrderWinMoney(){
        $where = [
            ['user_id', 'in', $this->user_ids],
            ['type', '=', 1]
        ];
        if($this->time_map)
            $where[] = ['betting_time', 'BETWEEN', $this->time_map];
        return $this->Cx_Game_Betting->where($where)->sum('win_money');
    }

    public function getServiceMoney(){
        return $this->Cx_Charge_logs->where('charge_user_id', '=', $this->user_id)->sum('money');
    }

    protected function getBalance(){
        $where = [
            ['id', 'in', $this->user_ids],
        ];
        return $this->Cx_User->where($where)->sum('balance');
    }

    protected function getCommission(){
        $where = [
            ['id', 'in', $this->user_ids],
        ];
        return $this->Cx_User->where($where)->sum('commission');
    }

    protected function getWithdrawMoney($status){
        $where = [
            ['user_id', 'in', $this->user_ids],
            ['status', '=', $status],
        ];
        if($this->time_map)
            $where[] = ['create_time', 'BETWEEN', $this->time_map];
        return $this->Cx_Withdrawal_Record->where($where)->sum('money');
    }

}
