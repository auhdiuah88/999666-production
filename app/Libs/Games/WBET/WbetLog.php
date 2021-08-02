<?php

namespace App\Libs\Games\WBET;

use App\Libs\Games\GameStrategy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WbetLog extends GameStrategy
{

    public $_msg = "";
    public $_data = "";

    //给wbet平台发送登录信息
    public function launch($productId){
        //获取用户数据
        $user_id = getUserIdFromToken(getToken());
        $info = DB::table('users')->where("id",$user_id)->select("phone","balance","ip")->first();
        if(empty($info)){
            return [
                "code" => 2,
                "msg" => "用户不存在",
                "data" => "",
            ];
        }
        //创建用户
        $config = config("game.wbet");
        $url = $config["url"]."api/createmember";
        //拼接验证码
        $ukey = mt_rand(00000000,99999999);
        $signature = md5($config["operator_id"].$ukey.$info->phone.$ukey.$config["Key"]);
        $params = [
            "signature" => $signature,
            "account_id" => $info->phone,
            "operator_id" => $config["operator_id"],
            "ukey" => $ukey,
        ];
        $header[] = "Content-Type: application/json";
        Log::channel('kidebug')->info('wbet-userlog-return',[$params]);
        $res = $this->curl_post($url, $params,$header);
        Log::channel('kidebug')->info('wbet-userlog-return',[$res]);
        $res = json_decode($res,true);
        return $this->_data = $res;
    }

    //废弃，勿删
    public function userInfo(): bool
    {
        ##验签
        if(!$params = checkSign())
        {
            $this->_msg = '验签失败';
            $this->_data = [
                'retCode' => 1,
                'data' => []
            ];
            return false;
        }
        $user_id = getUserIdFromToken($params['token']);
        $info = DB::table('users')->where("id",$user_id)->select("phone","code","balance")->first();
        if(empty($info)){
            $this->_msg = '用户不存在';
            $this->_data = [
                'retCode' => 2,
                'data' => []
            ];
            return false;
        }
        $data = [
            'account' => (string)$info->phone,
            'name' => $info->code,
            'balance' => $info->balance * 100,
            'headerUrl' => 'https://api.goshop6.in/storage/common/v6.png',
        ];
        $this->_data = [
            'retCode' => 0,
            'data' => $data
        ];
        return true;
    }
}
