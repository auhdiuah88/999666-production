<?php


namespace App\Repositories\Api;


use App\Dictionary\SettingDic;
use App\Models\Cx_Settings;
use Illuminate\Support\Facades\Redis;

class SettingRepository
{

    private $Cx_Settings;

    public function __construct
    (
        Cx_Settings $cx_Settings
    )
    {
        $this->Cx_Settings = $cx_Settings;
    }

    /**
     * 获取提现配置
     * @return mixed
     */
    public function getWithdraw()
    {
        return $this->Cx_Settings->where("setting_key", "withdraw")->value('setting_value');
    }

    /**
     * 获取充值配置
     * @return mixed
     */
    public function getRecharge()
    {
        return $this->Cx_Settings->where("setting_key", "recharge")->value('setting_value');
    }

    /**
     * 获取ip注册检测配置
     * @return int|mixed
     */
    public function getIpSwitch()
    {
        $value = $this->getSettingValueByKey(SettingDic::key('IP_SWITCH'));
        return $value?(int)($value['ip_switch']):0;
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
