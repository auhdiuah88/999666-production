<?php


namespace App\Dictionary;


class SettingDic
{

    protected static $settingKeys = [
        'staff_id' => [
            'title' => '员工角色ID',
            'key' => 'staff_id'
        ],
        'GROUP_LEADER_ROLE_ID' => [
            'title' => '组长角色ID',
            'key' => 'GROUP_LEADER_ROLE_ID'
        ],
        'withdraw' => [
            'title' => '提现配置',
            'key' => 'withdraw'
        ],
        'recharge' => [
            'title' => '充值配置',
            'key' => 'recharge'
        ],
        'login_alert' => [
            'title' => '登陆弹窗信息',
            'key' => 'login_alert'
        ],
        'logout_alert' => [
            'title' => '未登陆弹窗信息',
            'key' => 'logout_alert'
        ],
        'SERVICE' => [
            'title' => '客服配置',
            'key' => 'service'
        ],
        'CRISP_WEBSITE_ID' => [
            'title' => '第三方客服配置',
            'key' => 'CRISP_WEBSITE_ID'
        ],
        'DOWNLOAD_APP' => [
            'title' => '安卓app',
            'key' => 'DOWNLOAD_APP'
        ],
        'PRIVACY_POLICY' => [
            'title' => 'about-us->Privacy Policy',
            'key' => 'PRIVACY_POLICY'
        ],
        'RISK_DISCLOSURE_AGREEMENT' => [
            'title' => 'about-us->Risk Disclosure Agreement',
            'key' => 'RISK_DISCLOSURE_AGREEMENT'
        ],
        'ABOUT_US' => [
            'title' => 'about-us->About Us',
            'key' => 'ABOUT_US'
        ],
        'IP_SWITCH' => [
            'title' => 'about-us->IP Switch',
            'key' => 'IP_SWITCH'
        ],
        'IS_CHECK_RECHARGE' => [
            'title' => 'about-us->Is Check Recharge',
            'key' => 'IS_CHECK_RECHARGE'
        ],
        'RECHARGE_REBATE' => [
            'title' => 'recharge rebate',
            'key' => 'RECHARGE_REBATE'
        ],
        'REGISTER' => [
            'title' => 'register',
            'key' => 'REGISTER'
        ],
        'ACTIVITY' => [
            'title' => 'activity',
            'key' => 'ACTIVITY'
        ],
        'INVITE_FRIENDS' => [
            'title' => 'invite friends',
            'key' => 'INVITE_FRIENDS'
        ],
        'SIGN_SETTING' => [
            'title' => 'sign setting',
            'key' => 'SIGN_SETTING'
        ],
        'RED_ENVELOPE_TASK' => [
            'title' => 'red envelope task',
            'key' => 'RED_ENVELOPE_TASK'
        ],
        'WITHDRAW_SAFE' => [
            'title' => 'withdraw safe',
            'key' => 'WITHDRAW_SAFE'
        ],
        'WITHDRAW_SERVICE_CHARGE' => [
            'title' => 'withdraw service charge',
            'key' => 'WITHDRAW_SERVICE_CHARGE'
        ],
        'INDEX_AD' => [
            'title' => 'index ad',
            'key' => 'INDEX_AD'
        ],
        'LOGO' => [
            'title' => 'logo',
            'key' => 'LOGO'
        ],
    ];

    public static function key($key): string
    {
        return self::$settingKeys[$key]['key'];
    }

}
