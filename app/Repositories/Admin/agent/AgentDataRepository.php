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
        if($this->time_map){
            return $this->Cx_User
                ->whereIn('id', $this->user_ids)
                ->whereBetween('reg_time', $this->time_map)
                ->count();
        }else{
            return $this->Cx_User
                ->whereIn('id', $this->user_ids)
                ->count();
        }
    }

    public function getActiveMemberNum(){
        if($this->time_map){
            return $this->Cx_Game_Betting
                ->whereIn('user_id', $this->user_ids)
                ->whereBetween('betting_time', $this->time_map)
                ->groupBy('user_id')
                ->count();
        }else{
            return $this->Cx_Game_Betting
                ->whereIn('user_id', $this->user_ids)
                ->groupBy('user_id')
                ->count();
        }
    }

    public function getUserIds(){
        return $this->Cx_User->where('invite_relation', 'like', '%-'. $this->user_id .'-%')->pluck('id');
    }

    public function getSourceTypeUserIds($sourceType)
    {
        return $this->Cx_User->where('invite_relation', 'like', '%-'. $this->user_id .'-%')->where('reg_source_id', '=', $sourceType)->pluck('id');
    }

    public function getFirstRechargeNum(){
        if($this->time_map){
            return $this->Cx_User_Recharge_logs
                ->whereIn('user_id', $this->user_ids)
                ->where('is_first_recharge', '=', 1)
                ->where('status', '=', 2)
                ->whereBetween('time', $this->time_map)
                ->count();
        }else{
            return $this->Cx_User_Recharge_logs
                ->whereIn('user_id', $this->user_ids)
                ->where('is_first_recharge', '=', 1)
                ->where('status', '=', 2)
                ->count();
        }
    }

    public function getRechargeMoney(){
        if($this->time_map){
            return $this->Cx_User_Recharge_logs
                ->whereIn('user_id', $this->user_ids)
                ->where('status',2)
                ->whereBetween('time',$this->time_map)
                ->sum('money');
        }else{
            return $this->Cx_User_Recharge_logs
                ->whereIn('user_id', $this->user_ids)
                ->where('status',2)
                ->sum('money');
        }
    }

    public function getBankCardRechargeMoney()
    {
        if($this->time_map){
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')
                ->leftJoin('users as u','u.id','=','ubl.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->where('ubl.type',16)
                ->whereBetween('ubl.time',$this->time_map)
                ->sum('ubl.money');
        }else{
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')
                ->leftJoin('users as u','u.id','=','ubl.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->where('ubl.type',16)
                ->sum('ubl.money');
        }
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
        if($this->time_map){
            return $this->Cx_Charge_logs
                ->whereIn('user_id', $this->user_ids)
                ->whereBetween('create_time', $this->time_map)
                ->sum('money');
        }else{
            return $this->Cx_Charge_logs
                ->whereIn('user_id', $this->user_ids)
                ->sum('cl.money');
        }
    }

    public function getSignMoney(){
        if($this->time_map){
            return $this->Cx_Sign_Order
                ->whereIn('user_id', $this->user_ids)
                ->whereBetween('start_time', $this->time_map)
                ->sum('amount');
        }else{
            return $this->Cx_Sign_Order
                ->whereIn('user_id', $this->user_ids)
                ->sum('amount');
        }
    }

    public function getReceiveSIgnMoney(){
        if($this->time_map){
            return $this->Cx_Sign_Order
                ->whereIn('user_id', $this->user_ids)
                ->whereBetween('start_time', $this->time_map)
                ->sum('yet_receive_count');
        }else{
            return $this->Cx_Sign_Order
                ->whereIn('user_id', $this->user_ids)
                ->sum('yet_receive_count');
        }
    }

    public function getGiveMoney(){
        if($this->time_map){
            return $this->Cx_User_Balance_Logs
                ->whereIn('user_id', $this->user_ids)
                ->where('type','=',8)
                ->whereBetween('time', $this->time_map)
                ->sum('money');
        }else{
            return $this->Cx_User_Balance_Logs
                ->whereIn('user_id', $this->user_ids)
                ->where('type','=',8)
                ->sum('money');
        }
    }

    public function getOrderNum(){
        if($this->time_map){
            return $this->Cx_Game_Betting
                ->whereIn('user_id', $this->user_ids)
                ->whereBetween('betting_time', $this->time_map)
                ->count();
        }else{
            return $this->Cx_Game_Betting
                ->whereIn('user_id', $this->user_ids)
                ->count();
        }
    }

    public function getOrderMoney(){
        if($this->time_map){
            return $this->Cx_Game_Betting
                ->whereIn('user_id', $this->user_ids)
                ->whereBetween('betting_time', $this->time_map)
                ->sum('money');
        }else{
            return $this->Cx_Game_Betting
                ->whereIn('user_id', $this->user_ids)
                ->sum('money');
        }
    }

    public function getBettingPeople()
    {
        if($this->time_map){
            return $this->Cx_Game_Betting
                ->from('game_betting as gb')
                ->leftJoin('users as u','u.id','=','gb.user_id')
                ->where('u.invite_relation','like','%-'. $this->user_id .'-%')
                ->whereBetween('gb.betting_time', $this->time_map)
                ->groupBy('gb.user_id')
                ->count('gb.user_id');
        }else{
            return $this->Cx_Game_Betting
                ->from('game_betting as gb')
                ->leftJoin('users as u','u.id','=','gb.user_id')
                ->where('u.invite_relation','like','%-'. $this->user_id .'-%')
                ->groupBy('gb.user_id')
                ->count('gb.user_id');
        }
    }

    public function getOrderWinMoney(){
        if($this->time_map){
            return $this->Cx_Game_Betting
                ->whereIn('user_id', $this->user_ids)
                ->where('type','=', 1)
                ->whereBetween('betting_time', $this->time_map)
                ->sum('win_money');
        }else{
            return $this->Cx_Game_Betting
                ->whereIn('user_id', $this->user_ids)
                ->where('type','=', 1)
                ->sum('win_money');
        }
    }

    public function getServiceMoney()
    {
        if($this->time_map){
            return $this->Cx_Game_Betting
                ->whereIn('user_id', $this->user_ids)
                ->whereBetween('betting_time', $this->time_map)
                ->sum('service_charge');
        }else{
            return $this->Cx_Game_Betting
                ->whereIn('user_id', $this->user_ids)
                ->sum('service_charge');
        }
    }

    protected function getBalance(){
        return $this->Cx_User->whereIn('id', $this->user_ids)->sum('balance');
    }

    protected function getCommission(){
        return $this->Cx_User->whereIn('id', $this->user_ids)->sum('commission');
    }

    protected function getWithdrawMoney($status){
        if($this->time_map){
            return $this->Cx_Withdrawal_Record
                ->whereIn('user_id', $this->user_ids)
                ->where('status','=',$status)
                ->whereBetween('create_time', $this->time_map)
                ->sum('money');
        }else{
            return $this->Cx_Withdrawal_Record
                ->whereIn('user_id', $this->user_ids)
                ->where('status','=',$status)
                ->sum('money');
        }
    }

}
