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

    public function getModel()
    {
        return $this->Cx_User_Balance_Logs->with([
            "user" => function ($query) {
                $query->select(["id", "phone"]);
            },
            "admin" => function ($query) {
                $query->select(["id", "nickname"]);
            }
        ]);
    }
}
