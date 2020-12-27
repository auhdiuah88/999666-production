<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Game;
use App\Models\Cx_Role;
use App\Models\Cx_Settings;
use App\Repositories\BaseRepository;

class SettingRepository extends BaseRepository
{

    private $Cx_Settings, $Cx_Role, $Cx_Game;

    public function __construct
    (
        Cx_Settings $cx_Settings,
        Cx_Role $cx_Role,
        Cx_Game $cx_Game
    )
    {
        $this->Cx_Settings = $cx_Settings;
        $this->Cx_Role = $cx_Role;
        $this->Cx_Game = $cx_Game;
    }

    /**
     * 检查role是否存在
     * @param $role_id
     * @return mixed
     */
    public function checkRole($role_id)
    {
        return $this->Cx_Role->where("id", $role_id)->first();
    }

    /**
     * 获取员工角色ID
     * @return mixed
     */
    public function getStaff()
    {
        return $this->Cx_Settings->where("setting_key", "staff_id")->first();
    }

    /**
     * 修改员工角色ID
     * @param $role_id
     * @return mixed
     */
    public function editStaff($role_id)
    {
        return $this->Cx_Settings->where("setting_key", "staff_id")->update(['setting_value'=>[
            'role_id' => $role_id
        ]]);
    }

    /**
     * 新增员工角色ID
     * @param $role_id
     * @return mixed
     */
    public function addStaff($role_id)
    {
        return $this->Cx_Settings->create(
            [
                'setting_key' => 'staff_id',
                'setting_value' => [
                    'role_id' => $role_id
                ]
            ]
        );
    }

    /**
     * 游戏开奖规则
     * @return mixed
     */
    public function gameRule()
    {
        return $this->Cx_Game->select(['id', 'name', 'open_type', 'date_kill', 'one_kill'])->get();
    }

    /**
     * 更新游戏开奖规则
     * @param $id
     * @param $data
     * @return mixed
     */
    public function setGameRule($id, $data)
    {
        return $this->Cx_Game->where("id", $id)->update($data);
    }

}
