<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Role;
use App\Models\Cx_Settings;
use App\Repositories\BaseRepository;

class SettingRepository extends BaseRepository
{
    const GROUP_LEADER_ROLE_KEY = 'GROUP_LEADER_ROLE_ID';
    private $Cx_Settings;
    private $Cx_Role;

    public function __construct
    (
        Cx_Settings $cx_Settings,
        Cx_Role $cx_Role
    )
    {
        $this->Cx_Settings = $cx_Settings;
        $this->Cx_Role = $cx_Role;
    }

    /**
     * 检查role是否存在
     * @param $role_id
     * @return mixed
     */
    public function checkRole($role_id){
        return $this->Cx_Role->where("id", $role_id)->first();
    }

    /**
     * 获取员工角色ID
     * @return mixed
     */
    public function getStaff(){
        return $this->Cx_Settings->where("setting_key", "staff_id")->first();
    }

    /**
     * 修改员工角色ID
     * @param $role_id
     * @return mixed
     */
    public function editStaff($role_id){
        return $this->Cx_Settings->where("setting_key", "staff_id")->update(['setting_value'=>[
            'role_id' => $role_id
        ]]);
    }

    /**
     * 新增员工角色ID
     * @param $role_id
     * @return mixed
     */
    public function addStaff($role_id){
        return $this->Cx_Settings->create(
            [
                'setting_key' => 'staff_id',
                'setting_value' => [
                    'role_id' => $role_id
                ]
            ]
        );
    }

    public function getSettingByKey($key)
    {
        return $this->Cx_Settings->where("setting_key", $key)->first();
    }

    public function saveSetting($key, array $value)
    {
        return $this->Cx_Settings->updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }
}
