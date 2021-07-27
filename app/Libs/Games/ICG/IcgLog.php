<?php
namespace App\Libs\Games\ICG;

use App\Libs\Games\GameStrategy;
use http\Env\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Libs\Aes;

class IcgLog extends GameStrategy
{
    public $_msg = "";
    public $_data = "";

    //给ICG平台发送登录信息
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

        $config = config("game.icg");

        //获取秘钥
        $gettoken = $this->GetToken();
        return $this->_data = $gettoken;
    }

    //获取秘钥
    public function GetToken(){
        $config = config("game.icg");
        $url = $config["url"]."login";
        $params = [
            "username" => $config["username"],
            "password" => $config["password"],
        ];
        $res = $this->doRequest($url, $params);
        Log::channel('kidebug')->info('icg-GetToken-return',[$res]);
        $res = json_decode($res,true);
        return $res;
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
