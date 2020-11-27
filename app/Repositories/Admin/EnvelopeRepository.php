<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User_Balance_Logs;
use App\Repositories\BaseRepository;

class EnvelopeRepository extends BaseRepository
{
    private $Cx_User_Balance_Logs;

    public function __construct(Cx_User_Balance_Logs $balance_Logs)
    {
        $this->Cx_User_Balance_Logs = $balance_Logs;
    }

    public function findAll($offset, $limit)
    {
        return $this->getModel()->orderByDesc("time")->where("type", 5)->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countAll()
    {
        return $this->Cx_User_Balance_Logs->where("type", 5)->count();
    }

    public function searchEnvelope($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->getModel())->orderByDesc("time")->where("type", 5)->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countSearchEnvelope($data)
    {
        return $this->whereCondition($data, $this->getModel())->where("type", 5)->count("id");
    }

    public function getModel()
    {
        return $this->Cx_User_Balance_Logs->with(["user" => function ($query) {
            $query->select(["id", "phone", "nickname", "rec_ok_count"]);
        }]);
    }
}
