<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Settings;
use App\Repositories\BaseRepository;

class SettingRepository extends BaseRepository
{

    private $Cx_Settings;

    public function __construct
    (
        Cx_Settings $cx_Settings
    )
    {
        $this->Cx_Settings = $cx_Settings;
    }

    /**
     * 获取员工角色ID
     * @return mixed
     */
    public function getStaff(){
        return $this->Cx_Settings->where("setting_key", "staff_id")->value("setting_value");
    }
}
