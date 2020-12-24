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

    /**
     * 当日会员盈利榜
     * @param $where
     * @param $size
     * @param $page
     * @return array
     */
    public function dailyWinRank($where, $size, $page)
    {
        $prefix = DB::getConfig('prefix');
        $offset = ($page - 1) * $size;
        $list = DB::select('select sum(gb.win_money - gb.money) as cha, sum(gb.money) as total_betting_money, sum(gb.win_money) as total_win_money, sum(gb.service_charge) as total_service_charge, gb.user_id, u.phone from `'.$prefix.'game_betting` gb left join `'.$prefix.'users` u on gb.user_id = u.id '. $where .' group by user_id having cha > 0 order by cha desc limit '. $offset .','.$size);
        $total = count(DB::select('select count(*) from `'.$prefix.'game_betting` gb '. $where.' group by user_id'));
        return compact('list','total');
    }

    /**
     * 当日会员亏损榜
     * @param $where
     * @param $size
     * @param $page
     * @return array
     */
    public function dailyLoseRank($where, $size, $page)
    {
        $prefix = DB::getConfig('prefix');
        $offset = ($page - 1) * $size;
        $list = DB::select('select sum(gb.money - gb.win_money) as cha, sum(gb.money) as total_betting_money, sum(gb.win_money) as total_win_money, sum(gb.service_charge) as total_service_charge, gb.user_id, u.phone from `'.$prefix.'game_betting` gb left join `'.$prefix.'users` u on gb.user_id = u.id '. $where .' group by user_id having cha > 0 order by cha desc limit '. $offset .','.$size);
        $total = count(DB::select('select count(*) from `'.$prefix.'game_betting` bg '. $where.' group by user_id'));
        return compact('list','total');
    }

}
