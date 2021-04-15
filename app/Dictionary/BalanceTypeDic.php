<?php


namespace App\Dictionary;


class BalanceTypeDic
{

    //1.下注 2.充值 3.提现 4.签到礼金 5.红包礼金 6.投注获胜 7.签到零回扣 8.后台赠送礼金 9.手动上分 10.手动下分 11.提现驳回
    protected static $type = [
        '1' => [
            'value' => 1,
            'name' => '下注'
        ],
        '2' => [
            'value' => 2,
            'name' => '充值'
        ],
        '3' => [
            'value' => 3,
            'name' => '提现'
        ],
        '4' => [
            'value' => 4,
            'name' => '签到礼金'
        ],
        '5' => [
            'value' => 5,
            'name' => '红包礼金'
        ],
        '6' => [
            'value' => 6,
            'name' => '投注获胜'
        ],
        '7' => [
            'value' => 7,
            'name' => '签到领回扣'
        ],
        '8' => [
            'value' => 8,
            'name' => '后台赠送礼金'
        ],
        '9' => [
            'value' => 9,
            'name' => '手动上分'
        ],
        '10' => [
            'value' => 10,
            'name' => '手动下分'
        ],
        '11' => [
            'value' => 11,
            'name' => '提现驳回'
        ],
        '12' => [
            'value' => 12,
            'name' => '佣金提现'
        ],
        '13' => [
            'value' => 13,
            'name' => '打码量兑换余额'
        ],
        '14' => [
            'value' => 14,
            'name' => '充值返彩金'
        ],
        '15' => [
            'value' => 15,
            'name' => '注册送彩金'
        ],
        '16' => [
            'value' => 16,
            'name' => '银行卡充值'
        ],
    ];

    public static function data(int $type){
        return self::$type[$type];
    }

    public static function lists():array
    {
        return self::$type;
    }

    protected static $inType = [
        2, 4, 5, 6, 7, 8, 9, 11, 13, 14, 15, 16
    ];

    protected static $ouType = [
        1, 3, 10, 12
    ];

    public static function getType($flag=1): array
    {
        return $flag == 1 ? self::$inType : self::$ouType;
    }

}
