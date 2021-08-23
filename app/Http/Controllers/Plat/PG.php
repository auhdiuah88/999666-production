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
        header("Content-type: application/json",true);
        $res["operator_token"] = $_POST["operator_token"];
        $res["operator_player_session"] = $_POST["operator_player_session"];
        $res["secret_key"] = $_POST["secret_key"];
        Log::channel('kidebug')->info('pg-VerifySession',[$res,true]);
        $config = config("game.pg");
        //判断operator_token是否匹配
        if($res["operator_token"] != $config["operator_token"]){
            $msg = [
                "data" => null,
                "error" => [
                    "code" => "1034",
                    "message" => "no operator_token"
                ]
            ];
            echo json_encode($msg,true);
            die();
        }
        //判断secret_key是否匹配
        if($res["secret_key"] != $config["secret_key"]){
            $msg = [
                "data" => null,
                "error" => [
                    "code" => "1034",
                    "message" => "no secret_key"
                ]
            ];
            echo json_encode($msg,true);
            die();
        }
        //通过token查找用户
        $user = DB::table("users")->where("id",$res["operator_player_session"])->select()->first();
        //判断用户token是否存在
        if(!$user){
            $msg = [
                "data" => null,
                "error" => [
                    "code" => "1034",
                    "message" => "no token"
                ]
            ];
            echo json_encode($msg,true);
            die();
        }
        //判断token是否匹配
        if($res["operator_player_session"] != $user->id){
            $msg = [
                "data" => null,
                "error" => [
                    "code" => "1034",
                    "message" => "no operator_player_session"
                ]
            ];
            echo json_encode($msg,true);
            die();
        }
        $msg = [
            "data" => [
                "player_name" => "$user->phone",
                "currency" => "VND",
            ],
            "error" => null
        ];
        echo json_encode($msg,true);
        die();
    }

}
