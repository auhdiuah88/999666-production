<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Services\Api\SystemService;

class SystemController extends Controller
{
    private $SystemService;

    public function __construct(SystemService $systemService)
    {
        $this->SystemService = $systemService;
    }

    public function getWhatsAppGroupUrl()
    {
        $this->SystemService->getGroupUrl();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function getWhatsServiceUrl()
    {
        $this->SystemService->getServiceUrl();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function h5Alert()
    {
        $this->SystemService->getH5Alert();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function activity()
    {
        $this->SystemService->activity();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function serviceSetting()
    {
        $this->SystemService->serviceSetting();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function crispSetting()
    {
        $this->SystemService->getCrisp();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function appSetting()
    {
        $this->SystemService->getDownloadAppLink();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function aboutUsSetting(int $type)
    {
        $this->SystemService->getAboutUsSetting($type);
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function language()
    {
        $lang = [
            [
                'text' => 'English',
                'color' => 'blue',
                'fontSize' => 28,
                'language' => 'en'
            ],
        ];
        $country = env('COUNTRY','india');
        switch($country){
            case 'india':
                $lang[] = [
                    'text' => 'हिंदी',
                    'color' => 'blue',
                    'fontSize' => 28,
                    'language' => 'fr'
                ];
                break;
            case 'vn':
                $lang[] = [
                    'text' => 'ViệtName',
                    'color' => 'blue',
                    'fontSize' => 28,
                    'language' => 'vn'
                ];
                break;
            case 'br':
                $lang[] = [
                    'text' => 'Brasil',
                    'color' => 'blue',
                    'fontSize' => 28,
                    'language' => 'br'
                ];
                break;
        }
        return $this->AppReturn(
            200,
            'ok',
            $lang
        );
    }

    public function basicSetting()
    {
        $currency_arr = [
            'india' => '₹',
            'vn' => '₫',
            'br' => 'R$',
        ];
        $country = env('COUNTRY','india');
        $currency = $currency_arr[$country];

        $is_check_sms_code = env('IS_CHECK_SMS_CODE',true);
        $can_edit_bankcard = env('CAN_EDIT_BANKCARD',true);
        return $this->AppReturn(
            200,
            'ok',
            compact('currency','is_check_sms_code','can_edit_bankcard')
        );
    }

    public function indexAd()
    {
        $this->SystemService->getIndexAd();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function agentUrl()
    {
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            env('AGENT_URL','')
        );
    }

    public function logo()
    {
        $this->SystemService->logo();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function bettingFee()
    {
        $this->SystemService->bettingFee();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

    public function bettingRule()
    {
        $this->SystemService->bettingRule();
        return $this->AppReturn(
            $this->SystemService->_code,
            $this->SystemService->_msg,
            $this->SystemService->_data
        );
    }

}
