<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Game_Betting;
use App\Models\Cx_User;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class WagerRepository extends BaseRepository
{
    private $Cx_User, $Cx_Game_Betting;

    public function __construct(Cx_User $cx_User, Cx_Game_Betting $betting)
    {
        $this->Cx_User = $cx_User;
        $this->Cx_Game_Betting = $betting;
    }

    public function findAll($offset, $limit)
    {
        return $this->Cx_User
            ->select(["id", "phone", "nickname", "balance", "commission", "cl_withdrawal", "total_recharge", "is_login", "cl_betting", "cl_betting_total"])
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $betting = $this->Cx_Game_Betting->where("user_id", $item->id)->first();
                if (is_null($betting)) {
                    $item->betting_time = 0;
                } else {
                    $item->betting_time = $betting->betting_time;
                }
                return $item;
            });
    }

    public function countAll()
    {
        return $this->Cx_User->count("id");
    }

    public function searchWager($data, $betting_time, $offset, $limit)
    {
        return $this->whereCondition($data, $this->Cx_User)
            ->select(["id", "phone", "nickname", "balance", "commission", "cl_withdrawal", "total_recharge", "is_login", "cl_betting", "cl_betting_total"])
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($item) use ($betting_time) {
                $betting = $this->Cx_Game_Betting->where("user_id", $item->id)->where("betting_time", "<", $betting_time)->orderByDesc("betting_time")->first();
                if (is_null($betting)) {
                    $item->betting_time = 0;
                } else {
                    $item->betting_time = $betting->betting_time;
                }
                return $item;
            });
    }

    public function countWager($data)
    {
        return $this->whereCondition($data, $this->Cx_User)->count("id");
    }
}
