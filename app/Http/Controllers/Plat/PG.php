<?php
namespace App\Http\Controllers\Plat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Libs\Games\PG\PgLog;
use Illuminate\Support\Facades\Crypt;

class PG extends Controller{
    private $Pg;

    public function __construct
    (
        PgLog $Pg
    )
    {
        $this->Pg = $Pg;
    }

    //PG令牌验证
    public function VerifySession(Request $request){
        $res = $request->input();
        Log::channel('kidebug')->info('pg-VerifySession',[json_encode($res,true)]);
        $config = config("game.pg");
        //判断operator_token是否匹配
        if($res["operator_token"] != $config["operator_token"]){
            $msg = [
                "data" => "null",
                "error" => [
                    "code" => "1034",
                    "message" => "无效请求"
                ]
            ];
            return json_encode($msg,true);
        }
        //判断secret_key是否匹配
        if($res["secret_key"] != $config["secret_key"]){
            $msg = [
                "data" => "null",
                "error" => [
                    "code" => "1034",
                    "message" => "无效请求"
                ]
            ];
            return json_encode($msg,true);
        }
        //通过token查找用户
        $user = DB::table("users")->where("token",$res["operator_player_session"])->select()->first();
        //判断用户token是否存在
        if(!$user){
            $msg = [
                "data" => "null",
                "error" => [
                    "code" => "1034",
                    "message" => "无效请求"
                ]
            ];
            return json_encode($msg,true);
        }
        //判断token是否匹配
        if($res["operator_player_session"] != $user->token){
            $msg = [
                "data" => "null",
                "error" => [
                    "code" => "1034",
                    "message" => "无效请求"
                ]
            ];
            return json_encode($msg,true);
        }
        $msg = [
            "data" => [
                "player_name" => $user->phone,
                "currency" => "VND",
            ],
            "error" => "null"
        ];
        return json_encode($msg,true);
    }

    //查询玩家PG平台余额
    public function PGQueryScore(Request $request){
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        //调用查询接口
        $list = $this->Pg->PgQueryScore($data[0]);
        if(!$list){
            return [
                "code" => 404,
                "msg" => "link error",
            ];
        }
        return $list;
    }

    //上分
    public function PGUserTopScores(Request $request){
        $money = $request->input("p");//要上分的金额
        $money = json_decode(aesDecrypt($money),true);
        $money = $money["money"];
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        //调用下分
        $list = $this->Pg->PGUserTopScores($data[0],$money);
        if(!$list){
            return [
                "code" => 404,
                "msg" => "link error",
            ];
        }
        return $list;
    }

    //下分
    public function PGUserLowerScores(Request $request){
        $money = $request->input("p");//要上分的金额
        $money = json_decode(aesDecrypt($money),true);
        $money = $money["money"];
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        //调用下分
        $list = $this->Pg->PGUserLowerScores($data[0],$money);
        if(!$list){
            return [
                "code" => 404,
                "msg" => "link error",
            ];
        }
        return $list;
    }
}
