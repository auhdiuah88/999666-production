<?php


namespace App\Dictionary;


class UploadDic
{

    protected static $type = [
        '1' => [
            'value' => 1,
            'title' => 'image'
        ],
        '2' => [
            'value' => 2,
            'title' => 'jpeg'
        ],
        '3' => [
            'value' => 3,
            'title' => 'png'
        ]
    ];

    public static function data(int $type){
        return self::$type[$type];
    }

    public static function getTypes(){
        return self::$type;
    }

    public static function values():array
    {
        return array_map('array_shift',self::$type);
    }

    public static function titles():array
    {
        return array_map('end',self::$type);
    }

    public static function getType(string $ext)
    {
        foreach(self::$type as $key => $item){
            if($item['title'] == $ext)return $key;
        }
        return 0;
    }

}
