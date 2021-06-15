<?php


namespace App\Services\Api;

use App\Dictionary\SettingDic;
use App\Models\Cx_Banner;
use App\Repositories\Api\BannerRepository;
use App\Repositories\Api\SystemRepository;
use App\Services\BaseService as Service;

class SystemService extends Service
{
    private $SystemRepository, $BannerRepository;

    public function __construct
    (
        SystemRepository $repository,
        BannerRepository $bannerRepository
    )
    {
        $this->SystemRepository = $repository;
        $this->BannerRepository = $bannerRepository;
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
        if($login_value){
            $login_value['content'] = getHtml($login_value['content']);
            $login_value['status'] = $login_value['status'] ?? 1;
        }else{
            $login_value = [
                'content' => '',
                'btn' => [
                    'left' => [
                        'text' => '',
                        'link' => ''
                    ],
                    'right' => [
                        'text' => '',
                        'link' => ''
                    ],
                ],
            ];
        }
        if($logout_value){
            $logout_value['content'] = getHtml($logout_value['content']);
            $logout_value['status'] = $logout_value['status'] ?? 1;
        }
        $login_alert = $login_value;
        $logout_alert = $logout_value;
        $this->_data = compact('login_alert','logout_alert');
    }

    public function activity()
    {
        $banners = $this->BannerRepository->bannersByLocation(Cx_Banner::LOCATION_LIST[2]['id']);

        $inviteSetting = $this->SystemRepository->getSettingValueByKey(SettingDic::key('INVITE_FRIENDS'));
        if(!$inviteSetting)
        {
            $inviteSetting = [
                'image_id' => 0,
                'image_url' => ''
            ];
        }
        $signSetting = $this->SystemRepository->getSettingValueByKey(SettingDic::key('SIGN_SETTING'));
        if (!$signSetting){
            $signSetting = [
                'image_id' => 0,
                'image_url' => '',
                'status' => 0
            ];
        }
        $taskSetting = $this->SystemRepository->getSettingValueByKey(SettingDic::key('RED_ENVELOPE_TASK'));
        if (!$taskSetting){
            $taskSetting = [
                'image_id' => 0,
                'image_url' => '',
                'status' => 0
            ];
        }

        $this->_data = compact('banners','inviteSetting','signSetting','taskSetting');
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
        if(!isset($data['image_id'])){
            $data['image_id'] = 0;
            $data['image_url'] = "";
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

    public function getIndexAd()
    {
        $data = $this->SystemRepository->getSettingValueByKey(SettingDic::key('INDEX_AD'));
        if (!$data){
            $data = [
                'status' => 0,
                'content' => ''
            ];
        }else{
            $data['content'] = htmlspecialchars_decode($data['content']);
        }
        $this->_data = $data;
    }

}
