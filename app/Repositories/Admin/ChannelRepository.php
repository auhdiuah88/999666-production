<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User_Recharge_Logs;
use App\Models\Cx_Withdrawal_Record;

class ChannelRepository
{

    private $Cx_Withdrawal_Record, $Cx_User_Recharge_Logs;

    public function __construct
    (
        Cx_Withdrawal_Record $cx_Withdrawal_Record,
        Cx_User_Recharge_Logs $cx_User_Recharge_Logs
    )
    {
        $this->Cx_Withdrawal_Record = $cx_Withdrawal_Record;
        $this->Cx_User_Recharge_Logs = $cx_User_Recharge_Logs;
    }

    public function statistics($channels, $timeMap)
    {
        $list = [];
        foreach($channels as $channel){
            $list[] = [
                'channel' => $channel,
                'statistics' => $this->doStatistics($channel, $timeMap)
            ];
        }
        $all = $this->sumAllChannel($timeMap);
        return compact('list','all');
    }

    public function doStatistics($channel, $timeMap):array
    {
        $recharge = $this->sumRechargeByChannel($channel, $timeMap);
        $withdraw = $this->sumWithdrawByChannel($channel, $timeMap);
        return compact('recharge','withdraw');
    }

    public function sumRechargeByChannel($channel, $timeMap)
    {
        $where = [
            'pay_type' => ['=', $channel],
            'status' => ['=', 2]
        ];
        return $this->sumRecharge($where, $timeMap);
    }

    public function sumWithdrawByChannel($channel, $timeMap):array
    {
        $where = [
            'with_type' => ['=', $channel],
            'status' => ['=', 1],
            'pay_status' => ['=', 1],
        ];
        return $this->sumWithdraw($where, $timeMap);
    }

    public function sumAllChannel($timeMap):array
    {
        return [
            'withdraw' => $this->sumAllChannelWithdraw($timeMap),
            'recharge' => $this->sumAllChannelRecharge($timeMap)
        ];
    }

    public function sumAllChannelRecharge($timeMap)
    {
        $where = [
            'status' => ['=', 2]
        ];
        return $this->sumRecharge($where, $timeMap);
    }

    public function sumAllChannelWithdraw($timeMap):array
    {
        $where = [
            'status' => ['=', 1],
            'pay_status' => ['=', 1],
        ];
        return $this->sumWithdraw($where, $timeMap);
    }

    protected function sumRecharge($where, $timeMap)
    {
        if($timeMap)$where['time'] = ['BETWEEN', $timeMap];
        return makeModel($where, $this->Cx_User_Recharge_Logs)->sum('money');
    }

    protected function sumWithdraw($where, $timeMap)
    {
        if($timeMap)$where['loan_time'] = ['BETWEEN', $timeMap];
        $model = makeModel($where, $this->Cx_Withdrawal_Record);
        $withdraw_money = $model->sum('money');
        $service_charge = $model->sum('service_charge');
        return compact('withdraw_money','service_charge');
    }

}
