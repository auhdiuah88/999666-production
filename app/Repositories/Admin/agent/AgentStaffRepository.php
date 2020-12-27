<?php


namespace App\Repositories\Admin\agent;


use App\Models\Cx_Game_Betting;
use App\Models\Cx_User;
use App\Models\Cx_User_Balance_Logs;

class AgentStaffRepository
{

    protected $Cx_User, $Cx_Game_Betting, $Cx_User_Balance_Logs;

    public function __construct
    (
        Cx_User $cx_User,
        Cx_Game_Betting  $cx_Game_Betting,
        Cx_User_Balance_Logs $cx_User_Balance_Logs
    )
    {
        $this->Cx_User = $cx_User;
        $this->Cx_Game_Betting = $cx_Game_Betting;
        $this->Cx_User_Balance_Logs = $cx_User_Balance_Logs;
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
        $user_ids = array_column($list,'id');
        $time_map = [day_start(), day_end()];
        foreach($list as &$item){
            $item['total_recharge'] = $this->Cx_User_Balance_Logs->whereIn("user_id", $user_ids)->where("type", "=", 2)->sum('money');
            $item['total_betting'] = $this->Cx_Game_Betting->whereIn("user_id", $user_ids)->sum('money');
            $item['total_win'] = $this->Cx_Game_Betting->whereIn("user_id", $user_ids)->sum('win_money');
            $item['total_win_money'] = $item['total_win'] - $item['total_betting'];

            $item['betting'] = $this->Cx_Game_Betting->whereIn("user_id", $user_ids)->whereBetween('betting_time', $time_map)->sum('money');
            $item['win'] = $this->Cx_Game_Betting->whereIn("user_id", $user_ids)->whereBetween('betting_time', $time_map)->sum('win_money');
            $item['recharge'] = $this->Cx_User_Balance_Logs->whereIn("user_id", $user_ids)->whereBetween('time', $time_map)->where("type", "=", 2)->sum('money');
            $item['win_money'] = $item['win'] - $item['betting'];
        }
        $total = $model->count();
        return compact('list','total');
    }

}
