<?php


namespace App\Services\Admin;


use App\Repositories\Admin\SettingRepository;
use App\Services\BaseService;

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
