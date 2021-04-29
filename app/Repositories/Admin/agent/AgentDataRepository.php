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
                ->where('invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->whereBetween('reg_time', $this->time_map)
                ->count();
        }else{
            return $this->Cx_User
                ->where('invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->count();
        }
    }

    public function getActiveMemberNum(){
        if($this->time_map){
            return $this->Cx_Game_Betting->from('game_betting as gb')
                ->leftJoin('users as u','u.id','=','gb.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->whereBetween('gb.betting_time', $this->time_map)
                ->groupBy('user_id')
                ->count();
        }else{
            return $this->Cx_Game_Betting->from('game_betting as gb')
                ->leftJoin('users as u','u.id','=','gb.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->groupBy('user_id')
                ->count();
        }
    }

    public function getUserIds(){
        return $this->Cx_User->where('invite_relation', 'like', '%-'. $this->user_id .'-%')->pluck('id');
    }

    public function getFirstRechargeNum(){
        if($this->time_map){
            return $this->Cx_User_Recharge_logs->from('user_recharge_logs as url')
                ->leftJoin('users as u','u.id','=','url.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->where('url.is_first_recharge', '=', 1)
                ->where('url.status', '=', 2)
                ->whereBetween('url.time', $this->time_map)
                ->count();
        }else{
            return $this->Cx_User_Recharge_logs->from('user_recharge_logs as url')
                ->leftJoin('users as u','u.id','=','url.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->where('url.is_first_recharge', '=', 1)
                ->where('url.status', '=', 2)
                ->count();
        }
    }

    public function getRechargeMoney(){
        if($this->time_map){
            return $this->Cx_User_Recharge_logs->from('user_recharge_logs as url')
                ->leftJoin('users as u','u.id','=','url.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->where('url.status',2)
                ->whereBetween('url.time',$this->time_map)
                ->sum('url.money');
        }else{
            return $this->Cx_User_Recharge_logs->from('user_recharge_logs as url')
                ->leftJoin('users as u','u.id','=','url.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->where('url.status',2)
                ->sum('url.money');
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
            return $this->Cx_Charge_logs->from('charge_logs as cl')
                ->leftJoin('users as u','u.id','=','cl.charge_user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->whereBetween('cl.create_time', $this->time_map)
                ->sum('cl.money');
        }else{
            return $this->Cx_Charge_logs->from('charge_logs as cl')
                ->leftJoin('users as u','u.id','=','cl.charge_user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->sum('cl.money');
        }
    }

    public function getSignMoney(){
        if($this->time_map){
            return $this->Cx_Sign_Order->from('sign_orders as so')
                ->leftJoin('users as u','u.id','=','so.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->whereBetween('so.start_time', $this->time_map)
                ->sum('so.amount');
        }else{
            return $this->Cx_Sign_Order->from('sign_orders as so')
                ->leftJoin('users as u','u.id','=','so.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->sum('so.amount');
        }
    }

    public function getReceiveSIgnMoney(){
        if($this->time_map){
            return $this->Cx_Sign_Order->from('sign_orders as so')
                ->leftJoin('users as u','u.id','=','so.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->whereBetween('so.start_time', $this->time_map)
                ->sum('so.yet_receive_count');
        }else{
            return $this->Cx_Sign_Order->from('sign_orders as so')
                ->leftJoin('users as u','u.id','=','so.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->sum('so.yet_receive_count');
        }
    }

    public function getGiveMoney(){
        if($this->time_map){
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')
                ->leftJoin('users as u','u.id','=','ubl.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->where('ubl.type','=',8)
                ->whereBetween('ubl.time', $this->time_map)
                ->sum('ubl.money');
        }else{
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')
                ->leftJoin('users as u','u.id','=','ubl.user_id')
                ->where('u.invite_relation', 'like', '%-'. $this->user_id .'-%')
                ->where('ubl.type','=',8)
                ->sum('ubl.money');
        }
    }

    public function getOrderNum(){
        if($this->time_map){
            return $this->Cx_Game_Betting
                ->from('game_betting as gb')
                ->leftJoin('users as u','u.id','=','gb.user_id')
                ->where('u.invite_relation','like','%-'. $this->user_id .'-%')
                ->whereBetween('gb.betting_time', $this->time_map)
                ->count();
        }else{
            return $this->Cx_Game_Betting
                ->from('game_betting as gb')
                ->leftJoin('users as u','u.id','=','gb.user_id')
                ->where('u.invite_relation','like','%-'. $this->user_id .'-%')
                ->count();
        }
    }

    public function getOrderMoney(){
        if($this->time_map){
            return $this->Cx_Game_Betting
                ->from('game_betting as gb')
                ->leftJoin('users as u','u.id','=','gb.user_id')
                ->where('u.invite_relation','like','%-'. $this->user_id .'-%')
                ->whereBetween('gb.betting_time', $this->time_map)
                ->sum('gb.money');
        }else{
            return $this->Cx_Game_Betting
                ->from('game_betting as gb')
                ->leftJoin('users as u','u.id','=','gb.user_id')
                ->where('u.invite_relation','like','%-'. $this->user_id .'-%')
                ->sum('gb.money');
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
                ->from('game_betting as gb')
                ->leftJoin('users as u','u.id','=','gb.user_id')
                ->where('gb.type','=', 1)
                ->where('u.invite_relation','like','%-'. $this->user_id .'-%')
                ->whereBetween('gb.betting_time', $this->time_map)
                ->sum('gb.win_money');
        }else{
            return $this->Cx_Game_Betting
                ->from('game_betting as gb')
                ->leftJoin('users as u','u.id','=','gb.user_id')
                ->where('gb.type','=', 1)
                ->where('u.invite_relation','like','%-'. $this->user_id .'-%')
                ->sum('gb.win_money');
        }
    }

    public function getServiceMoney()
    {
        if($this->time_map){
            return $this->Cx_Game_Betting
                ->from('game_betting as gb')
                ->leftJoin('users as u','u.id','=','gb.user_id')
                ->where('u.invite_relation','like','%-'. $this->user_id .'-%')
                ->whereBetween('gb.betting_time', $this->time_map)
                ->sum('gb.service_charge');
        }else{
            return $this->Cx_Game_Betting
                ->from('game_betting as gb')
                ->leftJoin('users as u','u.id','=','gb.user_id')
                ->where('u.invite_relation','like','%-'. $this->user_id .'-%')
                ->sum('gb.service_charge');
        }
    }

    protected function getBalance(){
        return $this->Cx_User->where('invite_relation','like','%-'. $this->user_id .'-%')->sum('balance');
    }

    protected function getCommission(){
        return $this->Cx_User->where('invite_relation','like','%-'. $this->user_id .'-%')->sum('commission');
    }

    protected function getWithdrawMoney($status){
        if($this->time_map){
            return $this->Cx_Withdrawal_Record->from('withdrawal_record as wr')
                ->leftJoin('users as u','u.id','=','wr.user_id')
                ->where('u.invite_relation','like','%-'. $this->user_id .'-%')
                ->where('wr.status','=',$status)
                ->whereBetween('wr.create_time', $this->time_map)
                ->sum('wr.money');
        }else{
            return $this->Cx_Withdrawal_Record->from('withdrawal_record as wr')
                ->leftJoin('users as u','u.id','=','wr.user_id')
                ->where('u.invite_relation','like','%-'. $this->user_id .'-%')
                ->where('wr.status','=',$status)
                ->sum('wr.money');
        }
    }

}
