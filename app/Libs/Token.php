<?php


namespace App\Libs;


use App\Models\Cx_Admin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redis;

class Token
{

    protected static $admin = "ADMIN:";
    protected static $user = "ADMIN_USER:";

    public static function makeToken($id): string
    {
        return Crypt::encrypt($id . "+" . time());
    }

    public static function updateAdminToken($adminId): string
    {
        $token = self::makeToken($adminId);
        $admin = Cache::rememberForever(self::$user . ':'. $adminId, function() use ($adminId){
            return Cx_Admin::where("id", '=', $adminId)->select(['id', 'username', 'nickname', 'status', 'role_id', 'user_id'])->first();
        });
        $admin->token = $token;
        Redis::setex(self::$user . $adminId, 60*60*2, json_encode($admin));
        return $token;
    }

}
