<?php


namespace App\Services\Api;

use App\Repositories\Api\SystemRepository;
use App\Services\BaseService as Service;

class SystemService extends Service
{
    private $SystemRepository;

    public function __construct(SystemRepository $repository)
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
}
