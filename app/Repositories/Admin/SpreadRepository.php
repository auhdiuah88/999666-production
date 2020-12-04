<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Game_Betting;
use App\Models\Cx_User;
use App\Models\Cx_User_Balance_Logs;
use App\Repositories\BaseRepository;

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

    public function findUsers($timeMap, $ids)
    {
        return $this->Cx_User_Balance_Logs->whereBetween("time", $timeMap)->whereIn("user_id", $ids)->select(["id", "user_id", "dq_balance"])->orderBy("time")->groupBy("user_id")->get()->map(function ($item) {
            $user = $this->Cx_User->where("id", $item->user_id)->select(["id", "phone", "balance"])->first();
            $user->profit_loss = $user->balance - $item->dq_balance;
            $item->user = $user;
            return $item;
        })->pluck("user");
    }

    public function sumServiceCharge($user_id, $timeMap)
    {
        return $this->Cx_Game_Betting->where("user_id", $user_id)->whereBetween("betting_time", $timeMap)->sum("service_charge");
    }
}
