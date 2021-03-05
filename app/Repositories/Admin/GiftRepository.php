<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User_Balance_Logs;
use App\Repositories\BaseRepository;

class GiftRepository extends BaseRepository
{
    private $Cx_User_Balance_Logs;

    public function __construct(Cx_User_Balance_Logs $balance_Logs)
    {
        $this->Cx_User_Balance_Logs = $balance_Logs;
    }

    public function findAll($offset, $limit)
    {
        return $this->getModel()->offset($offset)->limit($limit)->orderByDesc("time")->get()->toArray();
    }

    public function countAll()
    {
        return $this->getModel()->count("id");
    }

    public function searchGiftLogs($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->getModel())->offset($offset)->limit($limit)->orderByDesc("time")->get()->toArray();
    }

    public function countSearchGiftLogs($data)
    {
        return $this->whereCondition($data, $this->getModel())->count("id");
    }

    public function getModel()
    {
        return $this->Cx_User_Balance_Logs->with([
            "user" => function ($query) {
                $query->select(["id", "phone"])->withTrashed();
            },
            "admin" => function ($query) {
                $query->select(["id", "nickname"])->withTrashed();
            }
        ])->where("type", 8);
    }
}
