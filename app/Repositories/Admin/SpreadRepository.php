<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Game_Betting;
use App\Models\Cx_User;
use App\Models\Cx_User_Balance_Logs;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class SpreadRepository extends BaseRepository
{
    private $Cx_User_Balance_Logs, $Cx_User, $Cx_Game_Betting;

    public function __construct(Cx_User_Balance_Logs $balance_Logs, Cx_User $cx_User, Cx_Game_Betting $game_Betting)
    {
        $this->Cx_User_Balance_Logs = $balance_Logs;
        $this->Cx_User = $cx_User;
        $this->Cx_Game_Betting = $game_Betting;
    }

    public function getSystemUserIds()
    {
        return array_column($this->Cx_User->where("reg_source_id", "<>", 1)->get("id")->toArray(), "id");
    }

    public function findUsers($ids, $page, $size, $status)
    {
        $where = " where gb.betting_time BETWEEN " . day_start() ." AND " . day_end() . " ";
        $where .= " AND gb.type = 1 ";
        if($ids){
            $where .= " AND gb.user_id in ( " . implode(',',$ids) .")";
        }else{
            $where .= " AND gb.user_id in ( " . 0 .")";
        }

        $prefix = DB::getConfig('prefix');
        $offset = ($page - 1) * $size;
        $total_cha = $total_service = 0;
        if($status == 0){
            $list = DB::select('select sum(gb.win_money - gb.money) as cha, sum(gb.money) as total_betting_money, sum(gb.win_money) as total_win_money, sum(gb.service_charge) as total_service_charge, gb.user_id, u.phone from `'.$prefix.'game_betting` gb left join `'.$prefix.'users` u on gb.user_id = u.id '. $where .' group by user_id having cha > 0 order by cha desc limit '. $offset .','.$size);

            $total = count(DB::select('select sum(gb.win_money - gb.money) as cha from `'.$prefix.'game_betting` gb '. $where.' group by user_id having cha > 0'));
            $all = DB::select('select sum(gb.win_money - gb.money) as cha, sum(gb.money) as total_betting_money, sum(gb.win_money) as total_win_money, sum(gb.service_charge) as total_service_charge from `'.$prefix.'game_betting` gb '. $where .' group by user_id having cha > 0');
        }else{
            $list = DB::select('select sum(gb.money - gb.win_money) as cha, sum(gb.money) as total_betting_money, sum(gb.win_money) as total_win_money, sum(gb.service_charge) as total_service_charge, gb.user_id, u.phone from `'.$prefix.'game_betting` gb left join `'.$prefix.'users` u on gb.user_id = u.id '. $where .' group by user_id having cha > 0 order by cha desc limit '. $offset .','.$size);
            $total = count(DB::select('select sum(gb.money - gb.win_money) as cha from `'.$prefix.'game_betting` gb '. $where.' group by user_id having cha > 0'));
            $all = DB::select('select sum(gb.money - gb.win_money) as cha, sum(gb.money) as total_betting_money, sum(gb.win_money) as total_win_money, sum(gb.service_charge) as total_service_charge from `'.$prefix.'game_betting` gb '. $where .' group by user_id having cha > 0');
        }
        foreach($all as $key =>  $item){
            $total_cha += $item['cha'];
            $total_service += $item['total_service_charge'];
        }

        return compact('list','total','total_cha','total_service');

//        return $this->Cx_User_Balance_Logs->whereBetween("time", $timeMap)->whereIn("user_id", $ids)->select(["id", "user_id", "dq_balance"])->orderBy("time")->groupBy("user_id")->get()->map(function ($item) {
//            $user = $this->Cx_User->where("id", $item->user_id)->select(["id", "phone", "balance"])->first();
//            $user->profit_loss = $user->balance - $item->dq_balance;
//            $item->user = $user;
//            return $item;
//        })->pluck("user");
    }

    public function sumServiceCharge($user_id, $timeMap)
    {
        return $this->Cx_Game_Betting->where("user_id", $user_id)->whereBetween("betting_time", $timeMap)->sum("service_charge");
    }
}
