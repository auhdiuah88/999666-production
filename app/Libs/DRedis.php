<?php


namespace App\Libs;


use Predis\Client;

class DRedis
{

    private static $redis;
    private static $_instance = null; //定义单例模式的变量
    public static function getInstance() {
        if(empty(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$redis;
    }

    private function __construct(){
        $redisConfig = config('database.redis.default');
        self::$redis = new Client($redisConfig);
    }

    /**
     * 防止clone多个实例
     */
    private function __clone(){

    }

    /**
     * 防止反序列化
     */
    private function __wakeup(){

    }

}
