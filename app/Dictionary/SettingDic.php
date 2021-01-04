<?php


namespace App\Dictionary;


class SettingDic
{

    protected static $settingKeys = [
        'STAFF_ID' => [
            'title' => '员工角色ID',
            'key' => 'staff_id'
        ],
        'GROUP_LEADER_ROLE_ID' => [
            'title' => '组长角色ID',
            'key' => 'GROUP_LEADER_ROLE_ID'
        ],
        'WITHDRAW' => [
            'title' => '提现配置',
            'key' => 'withdraw'
        ],
        'RECHARGE' => [
            'title' => '充值配置',
            'key' => 'recharge'
        ],
        'LOGIN_ALERT' => [
            'title' => '登陆弹窗信息',
            'key' => 'login_alert'
        ],
        'LOGOUT_ALERT' => [
            'title' => '未登陆弹窗信息',
            'key' => 'logout_alert'
        ],
        'SERVICE' => [
            'title' => '客服配置',
            'key' => 'service'
        ],
    ];

    public static function key($key)
    {
        return self::$settingKeys[$key]['key'];
    }

}
