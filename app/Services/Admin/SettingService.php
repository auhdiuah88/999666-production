<?php


namespace App\Services\Admin;


use App\Dictionary\GameDic;
use App\Repositories\Admin\SettingRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class SettingService extends BaseService
{

    private $SettingRepository;

    public function __construct
    (
        SettingRepository $settingRepository
    )
    {
        $this->SettingRepository = $settingRepository;
    }

    /**
     * get staff role id
     * @return mixed
     */
    public function getStaffId(){
        $staff = $this->SettingRepository->getStaff();
        $this->_data = $staff ? ["role_id"=>$staff->setting_value['role_id']]: ["role_id"=>""] ;
        return true;
    }

    /**
     * edit staff role id
     * @return bool
     */
    public function editStaffId(){
        $role_id = $this->intInput('role_id');
        ##判断角色是否存在
        if(!$this->SettingRepository->checkRole($role_id)){
            $this->_code = 401;
            $this->_msg = "角色不存在";
            return false;
        }
        $role = $this->SettingRepository->getStaff();
        if($role){
            $res = $this->SettingRepository->editStaff($role_id);
        }else{
            $res = $this->SettingRepository->addStaff($role_id);
        }
        if($res === false){
            $this->_code = 401;
            $this->_msg = "操作失败";
            return false;
        }
        $this->_msg = "操作成功";
        return true;
    }

    public function gameRule()
    {
        $games = $this->SettingRepository->gameRule();
        $rules = array_values(GameDic::getOpenType());
        $this->_data = compact('games','rules');
    }

    public function setGameRule()
    {
        $id = $this->intInput('id');
        $open_type = $this->intInput('open_type');
        $date_kill = $this->floatInput('date_kill');
        $one_kill = $this->floatInput('one_kill');
        $data = compact('open_type','date_kill','one_kill');
        $res = $this->SettingRepository->setGameRule($id, $data);
        if($res === false){
            $this->_code = 403;
            $this->_msg = '操作失败';
            return false;
        }
        $this->_msg = '操作成功';
        return true;
    }

    public function withdrawConfig()
    {
        $withdraw_type = config('pay.withdraw',[]);
        $setting = $this->SettingRepository->getWithdraw();
        $setting_value = $setting['setting_value'];
        $config = [];
        foreach($withdraw_type as $key => $item){
            $config[] = [
                'type' => $key,
                'limit' => isset($setting_value[$key])?$setting_value[$key]['limit']:['max'=>0,'min'=>0],
                'btn' => isset($setting_value[$key])?$setting_value[$key]['btn']:[]
            ];
        }
        $this->_data = $config;
    }

    public function setWithdrawConfig()
    {
        $type = $this->strInput('type');
        $max = $this->intInput('max');
        $min = $this->intInput('min');
        if ($max <= $min) {
            $this->_code = 403;
            $this->_msg = '最高限制应高于最低限制';
            return false;
        }
        $btn = request()->post('btn');
        $withdraw_type = config('pay.withdraw', []);
        if (!isset($withdraw_type[$type])) {
            $this->_code = 403;
            $this->_msg = '提现类型不支持';
            return false;
        }
        $setting = $this->SettingRepository->getWithdraw();
        $setting_value = $setting['setting_value'];
        $config = [];
        foreach ($withdraw_type as $key => $item) {
            if ($key == $type) {
                $config[$key] = [
                    'type' => $key,
                    'limit' => ['max' => $max, 'min' => $min],
                    'btn' => array_values($btn)
                ];
            } else {
                $config[$key] = [
                    'type' => $key,
                    'limit' => isset($setting_value[$key]) ? $setting_value[$key]['limit'] : ['max' => 0, 'min' => 0],
                    'btn' => isset($setting_value[$key]) ? $setting_value[$key]['btn'] : []
                ];
            }
        }
        if (!$setting) {
            ##新增
            $res = $this->SettingRepository->addWithdrawConfig($config);
        } else {
            ##更新
            $res = $this->SettingRepository->setWithdrawConfig($config);
        }

        if ($res === false) {
            $this->_code = 403;
            $this->_msg = '操作失败';
            return false;
        }
        $this->_msg = '操作成功';
    }

    public function queryGroupLeaderRoleId()
    {
        $val = $this->SettingRepository->getSettingByKey(SettingRepository::GROUP_LEADER_ROLE_KEY);
        $this->_data = $val ? ["role_id"=>$val->setting_value['role_id']]: ["role_id"=>""] ;
        return true;
    }

    public function saveGroupLeaderRoleId()
    {
        $role_id = $this->intInput('role_id');
        ##判断角色是否存在
        if(!$this->SettingRepository->checkRole($role_id)){
            $this->_code = 401;
            $this->_msg = "角色不存在";
            return false;
        }
        $res = $this->SettingRepository->saveSetting(SettingRepository::GROUP_LEADER_ROLE_KEY, ['role_id' => $role_id]);
        if($res === false){
            $this->_code = 401;
            $this->_msg = "操作失败";
            return false;
        }
        $this->_msg = "操作成功";
        return true;
    }

}
