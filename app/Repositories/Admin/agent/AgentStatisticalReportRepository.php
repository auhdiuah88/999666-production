<?php


namespace App\Repositories\Admin\agent;


use App\Models\Cx_Game_Betting;
use Illuminate\Support\Facades\DB;

class AgentStatisticalReportRepository
{

    protected $Cx_Game_Betting;

    public function __construct
    (Cx_Game_Betting $cx_Game_Betting)
    {
        $this->Cx_Game_Betting = $cx_Game_Betting;
    }

    public function dailyWinRank($where, $user_ids, $size)
    {
//        return $this->Cx_Game_Betting
//            ->whereIntegerInRaw('user_id',$user_ids)
//            ->where($where)
//            ->select(['sum(money) as betting_money', 'sum(win_money) as win_money', 'user_id'])
//            ->groupBy('user_id')
//            ->having('betting_money', '>', 'win_money')
//            ->orderByDesc('lose_money')
//            ->paginate($size);
        $offset =
        return DB::select('select sum(money) as toal_betting_money, sum(win_money) as total_win_money, user_id from `cx_game_betting` group by user_id order by (total_win_money - toal_betting_money) desc');
    }

}
