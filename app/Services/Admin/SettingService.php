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
        $setting_value = $this->SettingRepository->getWithdraw();
        $config = [];
        foreach($withdraw_type as $key => $item){
            $config[] = [
                'type' => $key,
                'limit' => isset($setting_value[$key])?$setting_value[$key]['limit']:['max'=>0,'min'=>0],
                'btn' => isset($setting_value[$key])?$setting_value[$key]['btn']:[],
                'merchant_id' => isset($setting_value[$key]) && isset($setting_value[$key]['merchant_id'])?$setting_value[$key]['merchant_id']:"",
                'secret_key' => isset($setting_value[$key]) && isset($setting_value[$key]['secret_key'])?$setting_value[$key]['secret_key']:"",
                'status' => isset($setting_value[$key]) && isset($setting_value[$key]['status'])?$setting_value[$key]['status']:0,
                'start_week' => isset($setting_value[$key]) && isset($setting_value[$key]['start_week'])?$setting_value[$key]['start_week']:'',
                'end_week' => isset($setting_value[$key]) && isset($setting_value[$key]['end_week'])?$setting_value[$key]['end_week']:'',
                'during_time' => isset($setting_value[$key]) && isset($setting_value[$key]['during_time'])?$setting_value[$key]['during_time']:''
            ];
        }
        $this->_data = $config;
    }

    public function setWithdrawConfig()
    {
        $type = $this->strInput('type');
        $max = $this->intInput('max');
        $min = $this->intInput('min');
        $secret_key = $this->strInput('secret_key');
        $merchant_id = $this->strInput('merchant_id');
        $start_week = $this->strInput('start_week');
        $end_week = $this->strInput('end_week');
        $during_time = $this->strInput('during_time');
        $status = $this->intInput('status');
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
        $setting_value = $this->SettingRepository->getWithdraw();
        $config = [];
        foreach ($withdraw_type as $key => $item) {
            if ($key == $type) {
                $config[$key] = [
                    'type' => $key,
                    'limit' => ['max'=>$max,'min'=>$min],
                    'btn' => array_values($btn),
                    'merchant_id' => $merchant_id,
                    'secret_key' => $secret_key,
                    'status' => $status,
                    'start_week' => $start_week,
                    'end_week' => $end_week,
                    'during_time' => $during_time,
                ];
            } else {
                $config[$key] = [
                    'type' => $key,
                    'limit' => isset($setting_value[$key])?$setting_value[$key]['limit']:['max'=>0,'min'=>0],
                    'btn' => isset($setting_value[$key])?$setting_value[$key]['btn']:[],
                    'merchant_id' => isset($setting_value[$key]) && isset($setting_value[$key]['merchant_id'])?$setting_value[$key]['merchant_id']:"",
                    'secret_key' => isset($setting_value[$key]) && isset($setting_value[$key]['secret_key'])?$setting_value[$key]['secret_key']:"",
                    'status' => isset($setting_value[$key]) && isset($setting_value[$key]['status'])?$setting_value[$key]['status']:0,
                    'start_week' => isset($setting_value[$key]) && isset($setting_value[$key]['start_week'])?$setting_value[$key]['start_week']:'',
                    'end_week' => isset($setting_value[$key]) && isset($setting_value[$key]['end_week'])?$setting_value[$key]['end_week']:'',
                    'during_time' => isset($setting_value[$key]) && isset($setting_value[$key]['during_time'])?$setting_value[$key]['during_time']:''
                ];
            }
        }
        ##更新
        $res = $this->SettingRepository->saveSetting(SettingRepository::WITHDRAW_KEY, $config);

        if ($res === false) {
            $this->_code = 403;
            $this->_msg = '操作失败';
            return false;
        }
        $this->_msg = '操作成功';
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

    public function rechargeConfig()
    {
        $recharge_type = config('pay.recharge',[]);
        $setting_value = $this->SettingRepository->getRecharge();
        $config = [];
        foreach($recharge_type as $key){
            $config[] = [
                'type' => $key,
                'limit' => isset($setting_value[$key])?$setting_value[$key]['limit']:['max'=>0,'min'=>0],
                'btn' => isset($setting_value[$key])?$setting_value[$key]['btn']:[],
                'merchant_id' => isset($setting_value[$key]) && isset($setting_value[$key]['merchant_id'])?$setting_value[$key]['merchant_id']:"",
                'secret_key' => isset($setting_value[$key]) && isset($setting_value[$key]['secret_key'])?$setting_value[$key]['secret_key']:"",
                'status' => isset($setting_value[$key]) && isset($setting_value[$key]['status'])?$setting_value[$key]['status']:0
            ];
        }
        $this->_data = $config;
    }

    public function setRechargeConfig()
    {
        $type = $this->strInput('type');
        $max = $this->intInput('max');
        $min = $this->intInput('min');
        $secret_key = $this->strInput('secret_key');
        $merchant_id = $this->strInput('merchant_id');
        $status = $this->intInput('status');
        if ($max <= $min) {
            $this->_code = 403;
            $this->_msg = '最高限制应高于最低限制';
            return false;
        }
        $btn = request()->post('btn');
        $recharge_type = config('pay.recharge', []);
        $recharge_type2 = array_flip($recharge_type);
        if (!isset($recharge_type2[$type])) {
            $this->_code = 403;
            $this->_msg = '充值类型不支持';
            return false;
        }
        $setting_value = $this->SettingRepository->getRecharge();
        $config = [];
        foreach ($recharge_type as $key) {
            if ($key == $type) {
                $config[$key] = [
                    'type' => $key,
                    'limit' => ['max'=>$max,'min'=>$min],
                    'btn' => array_values($btn),
                    'merchant_id' => $merchant_id,
                    'secret_key' => $secret_key,
                    'status' => $status
                ];
            } else {
                $config[$key] = [
                    'type' => $key,
                    'limit' => isset($setting_value[$key])?$setting_value[$key]['limit']:['max'=>0,'min'=>0],
                    'btn' => isset($setting_value[$key])?$setting_value[$key]['btn']:[],
                    'merchant_id' => isset($setting_value[$key]) && isset($setting_value[$key]['merchant_id'])?$setting_value[$key]['merchant_id']:"",
                    'secret_key' => isset($setting_value[$key]) && isset($setting_value[$key]['secret_key'])?$setting_value[$key]['secret_key']:"",
                    'status' => isset($setting_value[$key]) && isset($setting_value[$key]['status'])?$setting_value[$key]['status']:0
                ];
            }
        }
        ##更新
        $res = $this->SettingRepository->saveSetting(SettingRepository::RECHARGE_KEY, $config);

        if ($res === false) {
            $this->_code = 403;
            $this->_msg = '操作失败';
            return false;
        }
        $this->_msg = '操作成功';
        return true;
    }

    public function h5AlertContent()
    {
        $loginAlertValue = $this->SettingRepository->getSettingValueByKey("login_alert");
        $logoutAlertValue = $this->SettingRepository->getSettingValueByKey("logout_alert");
        $loginAlert = $loginAlertValue ? getHtml($loginAlertValue['content']) : "";
        $logoutAlert = $logoutAlertValue ? getHtml($logoutAlertValue['content']) : "";
        $this->_data = compact('loginAlert','logoutAlert');
    }

    public function setH5AlertContent()
    {
        $type = $this->intInput('type',1);
        $content = $this->htmlInput('content');
        $key = "";
        switch ($type){
            case 1:
                $key = "login_alert";
                break;
            case 2:
                $key = "logout_alert";
                break;
            default:
                break;
        }
        $res = $this->SettingRepository->saveSetting($key, compact('content'));
        if($res === false){
            $this->_code = 403;
            $this->_msg = "操作失败";
            return false;
        }
        $this->_msg = "操作成功";
        return true;
    }

    public function serviceEdit()
    {
        $btn_1 = request()->post('btn_1');
        $btn_2 = request()->post('btn_2');
        $service = [
            'btn_1' => [
                'link' => str_filter($btn_1['link']),
                'title' => str_filter($btn_1['title']),
                'icon' => str_filter($btn_1['icon']),
            ],
            'btn_2' => [
                'link' => str_filter($btn_2['link']),
                'title' => str_filter($btn_2['title']),
                'icon' => str_filter($btn_2['icon']),
            ],
        ];
        $res = $this->SettingRepository->saveSetting('service', $service);
        if($res === false){
            $this->_code = 403;
            $this->_msg = '修改失败';
             return false;
        }
        $this->_msg = '修改成功';
        return true;
    }

    public function getService()
    {
        $this->_data = $this->SettingRepository->getSettingValueByKey("service");
    }

}
