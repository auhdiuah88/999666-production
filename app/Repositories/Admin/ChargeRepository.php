<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Charge_Logs;
use App\Models\Cx_User;
use App\Repositories\BaseRepository;

class ChargeRepository extends BaseRepository
{
    private $Cx_Charge_Logs, $Cx_User;

    public function __construct(Cx_Charge_Logs $charge_Logs, Cx_User $cx_User)
    {
        $this->Cx_Charge_Logs = $charge_Logs;
        $this->Cx_User = $cx_User;
    }

    public function findUserByLike($phone)
    {
        return $this->Cx_User->where("phone", "like", "%" . $phone . "%")->get("id")->toArray();
    }

    public function findAll($offset, $limit, $status)
    {
        return $this->getModel()
            ->where("type", $status)
            ->orderByDesc("create_time")
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $number = $this->Cx_Charge_Logs->where("charge_user_id", $item->charge_user_id)->count("id");
                $money = $this->Cx_Charge_Logs->where("charge_user_id", $item->charge_user_id)->sum("money");
                $item->number = $number;
                $item->money = $money;
                return $item;
            });
    }

    public function countAll($status)
    {
        return $this->Cx_Charge_Logs->where("type", $status)->count("id");
    }

    public function searchChargeLogs($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->getModel())
            ->orderByDesc("create_time")
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $number = $this->Cx_Charge_Logs->where("charge_user_id", $item->charge_user_id)->count("id");
                $money = $this->Cx_Charge_Logs->where("charge_user_id", $item->charge_user_id)->sum("money");
                $item->number = $number;
                $item->money = $money;
                return $item;
            });
    }

    public function countSearchChargeLogs($data)
    {
        return $this->whereCondition($data, $this->getModel())->count("id");
    }

    public function getModel()
    {
        return  $model = $this->Cx_Charge_Logs->with(["user" => function ($query) {
            $query->select(["id", "nickname", "phone"]);
        }, "charge_user" => function ($query) {
            $query->select(["id", "phone"]);
        }]);
    }
}
