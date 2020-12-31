<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Game;
use App\Models\Cx_Role;
use App\Models\Cx_Settings;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Redis;

class SettingRepository extends BaseRepository
{

    private $Cx_Settings, $Cx_Role, $Cx_Game;
    const WITHDRAW_KEY = 'withdraw';
    const GROUP_LEADER_ROLE_KEY = 'GROUP_LEADER_ROLE_ID'; //对应setting表的 setting_key 字段
    const RECHARGE_KEY = 'recharge';

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

    public function getLeader()
    {
        return $this->Cx_Settings->where("setting_key", self::GROUP_LEADER_ROLE_KEY)->first();
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
        $key = "GAME_CONFIG_{$id}";
        $res = $this->Cx_Game->where("id", $id)->update($data);
        if($res === false)return $res;
        $data = $this->Cx_Game->where("id", $id)->first();
        Redis::set($key, json_encode($data,JSON_UNESCAPED_UNICODE));
        return $res;
    }

    /**
     * 获取提现配置
     * @return mixed
     */
    public function getWithdraw()
    {
        return $this->Cx_Settings->where("setting_key", "withdraw")->value('setting_value');
    }

    /**
     * 获取充值配置
     * @return mixed
     */
    public function getRecharge()
    {
        return $this->Cx_Settings->where("setting_key", "recharge")->value('setting_value');
    }

    /**
     * 更新提现配置
     * @param $config
     * @return mixed
     */
    public function setWithdrawConfig($config)
    {
        return $this->Cx_Settings->where("setting_key", "withdraw")->update(["setting_value"=>json_encode($config)]);
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

    /**
     * 获取setting_value
     * @param $key
     * @return mixed
     */
    public function getSettingValueByKey($key)
    {
        return $this->Cx_Settings->where("setting_key", $key)->value("setting_value");
    }
}
