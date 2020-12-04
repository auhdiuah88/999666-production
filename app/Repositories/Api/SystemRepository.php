<?php


namespace App\Repositories\Api;


use App\Models\Cx_System;
use App\Repositories\BaseRepository;

class SystemRepository extends BaseRepository
{
    private $Cx_System;

    public function __construct(Cx_System $cx_System)
    {
        $this->Cx_System = $cx_System;
    }

    public function getGroupUrl()
    {
        return $this->Cx_System->select(["whats_group_url"])->first();
    }

    public function getServiceUrl()
    {
        return $this->Cx_System->select(["whats_service_url"])->first();
    }

    /**
     * 获取系统配置
     */
    public function getSystem()
    {
        return $this->Cx_System->first();
    }
}
