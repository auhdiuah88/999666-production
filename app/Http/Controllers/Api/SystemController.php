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

}
