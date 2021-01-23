<?php


namespace App\Services\Admin;


use App\Dictionary\SettingDic;
use App\Repositories\Admin\SettingRepository;
use App\Repositories\Admin\SystemRepository;
use App\Services\BaseService;

class SystemService extends BaseService
{
    private $SystemRepository, $SettingRepository;

    public function __construct
    (
        SystemRepository $systemRepository,
        SettingRepository $settingRepository
    )
    {
        $this->SystemRepository = $systemRepository;
        $this->SettingRepository = $settingRepository;
    }

    public function findAll()
    {
        $data = $this->SystemRepository->findAll();
        $ip_switch = $this->SettingRepository->getSettingValueByKey(SettingDic::key('IP_SWITCH'),false);
        if($ip_switch){
            $data->ip_switch = $ip_switch;
        }else{
            $data->ip_switch = [
                'ip_switch' => 0
            ];
        }
        $this->_data = $data;
    }

    public function editSystem($data)
    {
        $ipSwitch = $data['ipSwitch']??0;
        if(isset($data['ipSwitch']))unset($data['ipSwitch']);
        ##编辑setting
        $this->SettingRepository->saveSetting(SettingDic::key('IP_SWITCH'), ['ip_switch'=>$ipSwitch]);
        if ($this->SystemRepository->editSystem($data)) {
            $this->_msg = "编辑成功";
        } else {
            $this->_code = 402;
            $this->_msg = "编辑失败";
        }
    }
}
