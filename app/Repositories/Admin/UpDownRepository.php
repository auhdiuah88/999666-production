<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User_Balance_Logs;
use App\Repositories\BaseRepository;

class UpDownRepository extends BaseRepository
{
    private $Cx_User_Balance_Logs;

    public function __construct(Cx_User_Balance_Logs $balance_Logs)
    {
        $this->Cx_User_Balance_Logs = $balance_Logs;
    }

    public function findAll($offset, $limit)
    {
        return $this->getModel()->orderByDesc("time")->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countAll()
    {
        return $this->getModel()->count("id");
    }

    public function searchUpAndDownLogs($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->getModel())->orderByDesc("time")->offset($offset)->limit($limit)->toSql();
    }

    public function countSearchUpAndDownLogs($data)
    {
        return $this->whereCondition($data, $this->getModel())->count("id");
    }

    public function getModel()
    {
        return $this->Cx_User_Balance_Logs->with([
            "user" => function ($query) {
                $query->select(["id", "phone"]);
            },
            "admin" => function ($query) {
                $query->select(["id", "nickname"]);
            }
        ])->where("type", 9)->orWhere("type", 10);
    }
}
