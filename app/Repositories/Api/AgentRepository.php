<?php


namespace App\Repositories\Api;


use App\Models\Cx_User;
use App\Models\Cx_User_Recharge_Logs;
use App\Repositories\BaseRepository;

class AgentRepository extends BaseRepository
{
    private $Cx_User, $Cx_User_Recharge_Logs;

    public function __construct
    (
        Cx_User $cx_User,
        Cx_User_Recharge_Logs $cx_User_Recharge_Logs
    )
    {
        $this->Cx_User = $cx_User;
        $this->Cx_User_Recharge_Logs = $cx_User_Recharge_Logs;
    }

    public function findCommission($id)
    {
        return $this->Cx_User->where("id", $id)->select(["commission"])->first();
    }

    public function findOne($id)
    {
        return $this->Cx_User->where("id", $id)->select(["one_commission"])->first();
    }

    public function countOne($id)
    {
        return $this->Cx_User->where("one_recommend_id", $id)->count();
    }

    public function findTwo($id)
    {
        return $this->Cx_User->where("id", $id)->select(["two_commission"])->first();
    }

    public function countTwo($id)
    {
        return $this->Cx_User->where("two_recommend_id", $id)->count();
    }

    public function getExtensionUser($id, $offset, $limit)
    {
        return $this->Cx_User->where("one_recommend_id", $id)->orWhere("two_recommend_id", $id)->select(["id", "phone", "nickname"])->offset($offset)->limit($limit)->orderByDesc("reg_time")->get()->toArray();
    }

    public function countExtensionUser($id)
    {
        return $this->Cx_User->where("one_recommend_id", $id)->orWhere("two_recommend_id", $id)->count("id");
    }

    public function getRecommendRecharge($id, $type)
    {
        if($type == 1){
            $user_ids = $this->Cx_User->where("two_recommend_id", $id)->pluck('id');
        }else{
            $user_ids = $this->Cx_User->where("one_recommend_id", $id)->pluck('id');
        }
        return $this->Cx_User_Recharge_Logs->whereIn("user_id", $user_ids)->where("status", '=', 2)->sum('arrive_money');
    }
}
