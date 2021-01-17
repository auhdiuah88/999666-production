<?php


namespace App\Services\Api;

use App\Dictionary\SettingDic;
use App\Repositories\Api\SystemRepository;
use App\Services\BaseService as Service;

class SystemService extends Service
{
    private $SystemRepository;

    public function __construct
    (
        SystemRepository $repository
    )
    {
        $this->SystemRepository = $repository;
    }

    public function getGroupUrl()
    {
        $this->_data = $this->SystemRepository->getGroupUrl();
    }

    public function getServiceUrl()
    {
        $this->_data = $this->SystemRepository->getServiceUrl();
    }

    public function getH5Alert()
    {
        $login_value = $this->SystemRepository->getSettingValueByKey("login_alert");
        $logout_value = $this->SystemRepository->getSettingValueByKey("logout_alert");
        $login_alert = $login_value ? getHtml($login_value['content']) : "";
        $logout_alert = $logout_value ? getHtml($logout_value['content']) : "";
        $this->_data = compact('login_alert','logout_alert');
    }

    public function serviceSetting()
    {
        $data = $this->SystemRepository->getSettingValueByKey(SettingDic::key('SERVICE'));
        if(!$data){
            $data = [
                'btn_1' => [
                    'link' => '',
                    'title' => '',
                    'icon' => ''
                ],
                'btn_2' => [
                    'link' => '',
                    'title' => '',
                    'icon' => ''
                ],
            ];
        }
        $data = array_values($data);
        $this->_data = $data;
    }

    public function getCrisp()
    {
        $data = $this->SystemRepository->getSettingValueByKey(SettingDic::key('CRISP_WEBSITE_ID'));
        if (!$data){
            $data = [
                'status' => 0,
                'crisp_website_id' => ''
            ];
        }
        $this->_data = $data;
    }

    public function getDownloadAppLink()
    {
        $data = $this->SystemRepository->getSettingValueByKey(SettingDic::key('DOWNLOAD_APP'));
        if (!$data){
            $data = [
                'status' => 0,
                'link' => ''
            ];
        }
        $this->_data = $data;
    }

    public function getAboutUsSetting($type)
    {
        switch($type){
            case 1:
                $key = 'PRIVACY_POLICY';
                break;
            case 2:
                $key = 'RISK_DISCLOSURE_AGREEMENT';
                break;
            case 3:
                $key = 'ABOUT_US';
                break;
            default:
                $key = '';
                break;
        }
        $data = $this->SystemRepository->getSettingValueByKey(SettingDic::key($key));
        if (!$data){
            $data = [
                'title' => '',
                'content' => ''
            ];
        }else{
            $data['content'] = htmlspecialchars_decode($data['content']);
        }
        $this->_data = $data;
    }

}
