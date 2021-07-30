<?php
namespace App\Libs\Games\PG;

use App\Libs\Games\GameStrategy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PgLog extends GameStrategy{
    public $_msg = "";
    public $_data = "";

    //给PG平台发送登录信息
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
        $config = config("game.pg");
        //生成guid
        $guid = $this->getguid();

        //请求参数
        $params = [
            "operator_token" => $config["operator_token"],
            "secret_key" => $config["secret_key"],
            "player_name" => $info->phone,
            "currency" => 0
        ];

        //拼接url
        $url = $config["PgSoftAPIDomain"]."/v3/Player/Create?trace_id=".$guid;
//        $in_url = 'SERVER_NAME：'.$_SERVER['SERVER_NAME'];
//        $header = ["Host : $in_url"];
        $res = $this->curl_post($url, $params);
        Log::channel('kidebug')->info('icg-GetToken-return',[$res]);
        $res = json_decode($res,true);
        return $this->_data = $res;
    }

    //生成guid
    public function getguid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
            return $uuid;
        }
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
