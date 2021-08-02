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
     * 获取提现手续费配置
     * @return mixed
     */
    public function getWithdrawServiceCharge(): array
    {
        $data =  $this->Cx_Settings->where("setting_key", SettingDic::key('WITHDRAW_SERVICE_CHARGE'))->value('setting_value');
        if (!$data){
            $data = [
                'standard' => 1500,
                'charge' => 45,
                'percent' => 0.03,
                'status' => 1,
                'free_status' => 0,
                'free_times' => 0,
                'limit_times' => -1
            ];
        }
        if(!isset($data['limit_times']))$data['limit_times'] = -1;
        return $data;
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
     * 获取提现是否检测充值
     * @return int
     */
    public function getIsCheckRecharge()
    {
        $value = $this->getSettingValueByKey(SettingDic::key('IS_CHECK_RECHARGE'));
        return $value?(int)($value['is_check_recharge']):0;
    }

    /**
     * 获取投注手续费
     * @return int
     */
    public function getBettingSetting()
    {
        $value = $this->getSettingValueByKey(SettingDic::key('BETTING_SETTING'));
        return $value?$value['service_charge']:0.03;
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
