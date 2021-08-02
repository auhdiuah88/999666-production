<?php


namespace App\Repositories\Admin\agent;


use App\Models\Cx_Game_Betting;
use App\Models\Cx_User;
use App\Models\Cx_User_Balance_Logs;
use App\Models\Cx_Withdrawal_Record;

class AgentStaffRepository
{

    protected $Cx_User, $Cx_Game_Betting, $Cx_User_Balance_Logs, $Cx_Withdrawal_Record;

    public function __construct
    (
        Cx_User $cx_User,
        Cx_Game_Betting  $cx_Game_Betting,
        Cx_User_Balance_Logs $cx_User_Balance_Logs,
        Cx_Withdrawal_Record $cx_Withdrawal_Record
    )
    {
        $this->Cx_User = $cx_User;
        $this->Cx_Game_Betting = $cx_Game_Betting;
        $this->Cx_User_Balance_Logs = $cx_User_Balance_Logs;
        $this->Cx_Withdrawal_Record = $cx_Withdrawal_Record;
    }

    /**
     * 获取员工的数据
     * @param $where
     * @param $size
     * @param $page
     * @return mixed
     */
    public function getLists($where, $size, $page)
    {
        $model = makeModel($where, $this->Cx_User);
        $list = $model
            ->select(['id', 'nickname', 'phone', 'id as total_invite', 'id as invite', 'cl_withdrawal', 'balance', 'commission', 'one_number', 'two_number', 'one_commission', 'two_commission', 'cl_commission'])
            ->orderByDesc('total_recharge')
            ->orderByDesc('id')
            ->offset(($page-1)*$size)
            ->limit($size)
            ->get();
        if($list->isEmpty())
           return ['list'=>[], 'total'=>0];

        $list = $list->toArray();
        foreach($list as $key => $item){
            $list[$key]['total_recharge'] = '-1';
            $list[$key]['total_betting'] = '-1';
            $list[$key]['total_win_money'] = '-1';
            $list[$key]['recharge'] = '-1';
            $list[$key]['betting'] = '-1';
            $list[$key]['win_money'] = '-1';
            $list[$key]['withdraw'] = '-1';
        }
        $total = $model->count();
        return compact('list','total');
    }

    public function getFilterData($type, $user_id)
    {
        $time_map = [day_start(), day_end()];
        $user_ids = $this->Cx_User->where("invite_relation", "like", "%-{$user_id}-%")->pluck('id');
        switch ($type){
            case  1: //总充值
                $data = $this->filterTotalRecharge($user_ids);
                break;
            case 2:  //总投注
                $data = $this->filterTotalBetting($user_ids);
                break;
            case 3:  //总输赢
                $data = $this->filterTotalWinMoney($user_ids);
                break;
            case 4:  //今日充值
                $data = $this->filterRecharge($user_ids, $time_map);
                break;
            case 5:  //今日投注
                $data = $this->filterBetting($user_ids, $time_map);
                break;
            case 6:  //今日输赢
                $data = $this->filterWinMoney($user_ids, $time_map);
                break;
            case 7: //今日提现
                $data = $this->filterWithdraw($user_ids, $time_map);
                break;
            default:
                $data = 0;
                break;

        }
        return compact('data');
    }

    protected function filterTotalRecharge($user_ids)
    {
        return $this->Cx_User_Balance_Logs->whereIn("user_id", $user_ids)->where("type", "=", 2)->sum('money');
    }

    protected function filterTotalBetting($user_ids)
    {
        return $this->Cx_Game_Betting->whereIn("user_id", $user_ids)->sum('money');
    }

    protected function filterTotalWin($user_ids)
    {
        return $this->Cx_Game_Betting->whereIn("user_id", $user_ids)->sum('win_money');
    }

    protected function filterTotalWinMoney($user_ids)
    {
        $totalWin = $this->filterTotalWin($user_ids);
        $totalBetting = $this->filterTotalBetting($user_ids);
        $totalWinMoney = bcsub($totalWin, $totalBetting,2);
        if($totalWinMoney > 0)
            $totalWinMoney = "+" . $totalWinMoney;
        return $totalWinMoney;
    }

    protected function filterBetting($user_ids, $time_map)
    {
        return $this->Cx_Game_Betting->whereIn("user_id", $user_ids)->whereBetween('betting_time', $time_map)->sum('money');
    }

    protected function filterWin($user_ids, $time_map)
    {
        return $this->Cx_Game_Betting->whereIn("user_id", $user_ids)->whereBetween('betting_time', $time_map)->sum('win_money');
    }

    protected function filterRecharge($user_ids, $time_map)
    {
        return $this->Cx_User_Balance_Logs->whereIn("user_id", $user_ids)->whereBetween('time', $time_map)->where("type", "=", 2)->sum('money');
    }

    protected function filterWithdraw($user_ids, $time_map)
    {
        return $this->Cx_Withdrawal_Record->whereIn("user_id", $user_ids)->whereBetween('create_time', $time_map)->where("pay_status", "=", 1)->sum('money');
    }

    protected function filterWinMoney($user_ids, $time_map)
    {
        $winMoney = $this->filterWin($user_ids, $time_map) - $this->filterBetting($user_ids, $time_map);
        if($winMoney > 0)
            $winMoney = "+" . $winMoney;
        return $winMoney;
    }

}
