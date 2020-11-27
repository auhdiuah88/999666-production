<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User_Commission_Logs;
use App\Repositories\BaseRepository;

class CommissionRepository extends BaseRepository
{
    private $Cx_User_Commission_Logs;

    public function __construct(Cx_User_Commission_Logs $commission_Logs)
    {
        $this->Cx_User_Commission_Logs = $commission_Logs;
    }

    public function findAll($offset, $limit)
    {
        return $this->Cx_User_Commission_Logs->orderByDesc("time")->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countAll()
    {
        return $this->Cx_User_Commission_Logs->count("id");
    }

    public function searchCommissionLogs($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->Cx_User_Commission_Logs)->orderByDesc("time")->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countSearchCommissionLogs($data)
    {
        return $this->whereCondition($data, $this->Cx_User_Commission_Logs)->count("id");
    }
}
