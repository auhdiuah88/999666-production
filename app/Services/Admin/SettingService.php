<?php


namespace App\Services\Admin;


use App\Dictionary\GameDic;
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

    public function gameRule()
    {
        $games = $this->SettingRepository->gameRule();
        $rules = GameDic::getOpenType();
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

}
