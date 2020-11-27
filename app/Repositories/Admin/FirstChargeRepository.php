<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User;
use App\Models\Cx_User_Balance_Logs;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class FirstChargeRepository extends BaseRepository
{
    private $Cx_User, $Cx_User_Balance_Logs;

    public function __construct(Cx_User $cx_User, Cx_User_Balance_Logs $balance_Logs)
    {
        $this->Cx_User_Balance_Logs = $balance_Logs;
        $this->Cx_User = $cx_User;
    }

    public function findAll($offset, $limit)
    {
        return $this->Cx_User_Balance_Logs
            ->where("is_first_recharge", 1)
            ->select(["id", "time", "user_id"])
            ->offset($offset)
            ->limit($limit)
            ->orderByDesc("time")
            ->get()
            ->map(function ($item) {
                $user = $this->Cx_User->where("id", $item->user_id)->select(["id", "phone", "nickname", "total_recharge", "cl_withdrawal", "balance", "commission", "one_recommend_phone", "two_recommend_phone"])->first();
                $item->user = $user;
                return $item;
            });
    }

    public function countAll()
    {
        return $this->Cx_User_Balance_Logs->where("is_first_recharge", 1)->count();
    }

    public function searchChargeLogs($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->Cx_User_Balance_Logs)
            ->where("is_first_recharge", 1)
            ->select(["id", "time", "user_id"])
            ->offset($offset)
            ->limit($limit)
            ->orderByDesc("time")
            ->get()
            ->map(function ($item) {
                $user = $this->Cx_User->where("id", $item->user_id)->select(["id", "phone", "nickname", "total_recharge", "cl_withdrawal", "balance", "commission", "one_recommend_phone", "two_recommend_phone"])->first();
                $item->user = $user;
                return $item;
            });
    }

    public function countSearchChargeLogs($data)
    {
        return $this->whereCondition($data, $this->Cx_User_Balance_Logs)
            ->where("is_first_recharge", 1)
            ->count("id");
    }

    public function findUsers($phone)
    {
        return $this->Cx_User->where("phone", "like", "%".$phone."%")->get("id")->toArray();
    }
}
