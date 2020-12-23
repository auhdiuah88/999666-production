<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User_Recharge_Logs;
use App\Repositories\BaseRepository;

class RechargeRepository extends BaseRepository
{
    private $Cx_User_Recharge_Logs;

    public function __construct(Cx_User_Recharge_Logs $cx_User_Recharge_Logs)
    {
        $this->Cx_User_Recharge_Logs = $cx_User_Recharge_Logs;
    }

    public function findAll($offset, $limit, $status)
    {
        return $this->Cx_User_Recharge_Logs
            ->with(
                [
                    'user' => function($query){
                        $query->select(['id', 'code', 'phone', 'reg_time', 'balance', 'nickname']);
                    }
                ]
            )
            ->where("status", $status)
            ->offset($offset)
            ->limit($limit)
            ->orderByDesc("time")
            ->get()
            ->toArray();
    }

    public function countAll($status)
    {
        return $this->Cx_User_Recharge_Logs->where("status", $status)->count();
    }

    public function searchChargeLogs($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->Cx_User_Recharge_Logs)->offset($offset)->limit($limit)->orderByDesc("time")->get()->toArray();
    }

    public function countSearchChargeLogs($data)
    {
        return $this->whereCondition($data, $this->Cx_User_Recharge_Logs)->count("id");
    }
}
