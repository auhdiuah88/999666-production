<?php


namespace App\Dictionary;


class GameDic
{

    protected static $openType = [
        '1' => [
            'value' => 1,
            'title' => '天杀'
        ],
        '2' => [
            'value' => 2,
            'title' => '局杀'
        ],
        '3' => [
            'value' => 3,
            'title' => '随机'
        ]
    ];

    public static function data(int $type){
        return self::$openType[$type];
    }

}
