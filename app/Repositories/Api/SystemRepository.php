<?php


namespace App\Repositories\Api;


use App\Models\Cx_Settings;
use App\Models\Cx_System;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Redis;

class SystemRepository extends BaseRepository
{
    private $Cx_System, $Cx_Settings;

    public function __construct
    (
        Cx_System $cx_System,
        Cx_Settings $cx_Settings
    )
    {
        $this->Cx_System = $cx_System;
        $this->Cx_Settings = $cx_Settings;
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

    /**
     * 获取setting_value
     * @param $key
     * @return mixed
     */
    public function getSettingValueByKey1($key)
    {
        return $this->Cx_Settings->where("setting_key", $key)->value("setting_value");
    }

    /**
     * 获取setting_value
     * @param $key
     * @param bool $from_cache
     * @return mixed
     */
    public function getSettingValueByKey($key, $from_cache = true)
    {
        $func = function () use ($key) {
            return $this->Cx_Settings->where("setting_key", $key)->value("setting_value");
        };
        if ($from_cache) {
            if ($cacheRes = Redis::get($key)) {
                return json_decode($cacheRes, true);
            } else {
                $res = $func();
                if ($res) {
                    Redis::set($key, json_encode($res));
                }
                return $res;
            }
        }
        return $func();
    }
}
