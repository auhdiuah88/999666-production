<?php


namespace App\Repositories\Api;


use App\Models\Cx_Settings;

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

}
