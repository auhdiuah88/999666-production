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
            'vn' => '₫'
        ];
        $country = env('COUNTRY','india');
        $currency = $currency_arr[$country];

        $is_check_sms_code = env('IS_CHECK_SMS_CODE',true);
        return $this->AppReturn(
            200,
            'ok',
            compact('currency','is_check_sms_code')
        );
    }

}
