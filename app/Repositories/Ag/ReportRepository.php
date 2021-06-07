<?php


namespace App\Repositories\Ag;


use App\Models\Cx_Game_Betting;
use App\Models\Cx_User;
use App\Models\Cx_User_Recharge_Logs;
use App\Models\Cx_Withdrawal_Record;

class ReportRepository
{

    protected $Cx_Users, $Cx_Game_Betting, $Cx_User_Recharge_Logs, $Cx_Withdraw_Record;

    public $timestamp;
    public $user_ids;

    public function __construct
    (
        Cx_User $cx_User,
        Cx_Game_Betting $cx_Game_Betting,
        Cx_User_Recharge_Logs $cx_User_Recharge_Logs,
        Cx_Withdrawal_Record $cx_Withdrawal_Record
    )
    {
        $this->Cx_Users = $cx_User;
        $this->Cx_Game_Betting = $cx_Game_Betting;
        $this->Cx_User_Recharge_Logs = $cx_User_Recharge_Logs;
        $this->Cx_Withdraw_Record = $cx_Withdrawal_Record;
    }

    public function getAgReport(): array
    {
        $data = [
            'betting_money' => $this->sumBettingMoney(),
            'win_money' => $this->sumBettingWinMoney(),
            'commission' => $this->sumCommission(),
            'betting_member' => $this->countBettingMemberNum(),
            'first_recharge' => $this->countFirstRechargeMember(),
            'register_member' => $this->countRegisterMember(),
            'member' => $this->countMember(),
            'balance' => $this->sumBalance(),
            'recharge' => $this->sumRecharge(),
            'withdraw' => $this->sumWithdraw(),
        ];
        $data['profit'] = bcsub($data['win_money'], $data['betting_money'],2);
        return $data;
    }

    protected function makeModel($where, $model, $field="user_id")
    {
        $where[$field] = ['in', $this->user_ids];
        return makeModel($where, $model);
    }

    protected function sumBettingMoney()
    {
        $where = [
            'betting_time' => ['BETWEEN', $this->timestamp]
        ];
        return $this->makeModel($where, $this->Cx_Game_Betting)->sum('money');
    }

    protected function sumBettingWinMoney()
    {
        $where = [
            'betting_time' => ['BETWEEN', $this->timestamp],
            'status' => ['=', 1]
        ];
        return $this->makeModel($where, $this->Cx_Game_Betting)->sum('win_money');
    }

    protected function sumCommission()
    {
        $where = [
            'betting_time' => ['BETWEEN', $this->timestamp],
        ];
        return $this->makeModel($where, $this->Cx_Game_Betting)->sum('service_charge');
    }

    protected function countBettingMemberNum()
    {
        $where = [
            'betting_time' => ['BETWEEN', $this->timestamp],
        ];
        return $this->makeModel($where, $this->Cx_Game_Betting)->groupBy('user_id')->count('id');
    }

    protected function countFirstRechargeMember()
    {
        $where = [
            'status' => ['=', 2],
            'is_first_recharge' => ['=', 1],
            'arrive_time' => ['BETWEEN', $this->timestamp]
        ];
        return $this->makeModel($where, $this->Cx_User_Recharge_Logs)->groupBy('user_id')->count('id');
    }

    protected function countRegisterMember()
    {
        $where = [
            'reg_time' => ['BETWEEN', $this->timestamp],
        ];
        return $this->makeModel($where, $this->Cx_Users,'id')->count('id');
    }

    protected function countMember()
    {
        return $this->makeModel([], $this->Cx_Users,'id')->count('id');
    }

    protected function sumBalance()
    {
        return $this->makeModel([], $this->Cx_Users,'id')->sum('balance');
    }

    protected function sumRecharge()
    {
        $where = [
            'status' => ['=', 2],
            'arrive_time' => ['BETWEEN', $this->timestamp]
        ];
        return $this->makeModel($where, $this->Cx_User_Recharge_Logs)->sum('arrive_money');
    }

    protected function sumWithdraw()
    {
        $where = [
            'status' => ['=', 1],
            'pay_status' => ['=', 1],
            'loan_time' => ['BETWEEN', $this->timestamp],
        ];
        return $this->makeModel($where, $this->Cx_Withdraw_Record)->sum('money');
    }

}
