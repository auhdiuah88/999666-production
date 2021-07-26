<?php
namespace App\Http\Controllers\Plat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Libs\Aes;
use App\Libs\Games\V8\V8log;
use Illuminate\Support\Facades\Crypt;

class V8 extends Controller
{
    private $V8log;

    public function __construct
    (
        V8log $V8log
    )
    {
        $this->V8log = $V8log;
    }

    //查询余额
    public function Querymoney(Request $request){
        $url = $request->input();
        $config = config("game.v8");
        $aes = new Aes();
        $param = urldecode($aes->decryptno64($url["param"],$config["deskey"]));
        parse_str($param, $param);
        //效验
        if($url["agent"] != $config["agent"]){
            return [
                "code" => "2",
                "data" => "agent error",
            ];
        }
        $key = md5($config["agent"].$url["timestamp"].$config["md5key"]);
        if($key != $url["key"]){
            return [
                "code" => "3",
                "data" => "key error",
            ];
        }

        //查询用户余额
        $info = DB::table('users')->where("phone",$param["account"])->select("balance")->first();
        if(!$info){
            return [
                "code" => "4",
                "data" => "user error",
            ];
        }
        return [
            "code" => "0",
            "money" => $info->balance,
        ];
    }

    //上分请求申请
    public function V8TopScores(Request $request){
        $url = $request->input();
        $config = config("game.v8");
        $aes = new Aes();
        $param = urldecode($aes->decryptno64($url["param"],$config["deskey"]));
        parse_str($param, $param);
        //效验
        if($url["agent"] != $config["agent"]){
            return [
                "code" => "2",
                "data" => "agent error",
            ];
        }
        $key = md5($config["agent"].$url["timestamp"].$config["md5key"]);
        if($key != $url["key"]){
            return [
                "code" => "3",
                "data" => "key error",
            ];
        }
        //查询用户余额
        $info = DB::table('users')->where("phone",$param["account"])->select("balance")->first();
        //验证用户
        if(!$info){
            return [
                "code" => "4",
                "data" => "user error",
            ];
        }
        //验证余额
        if($info->balance < $param["money"]){
            return [
                "code" => "5",
                "data" => "Sorry, your credit is running low",
            ];
        }

        //扣除用户余额
        try {
            //用户自减上分金额
            $list = DB::table("users")->where("phone",$param["account"])->decrement("balance",$param["money"]);
            if(!$list){
                return [
                    "code" => "6",
                    "data" => "money error",
                ];
            }
            return [
                "code" => "0",
            ];
        }catch(\Exception $e){
            return [
                "code" => "6",
                "data" => $e->getMessage(),
            ];
        }
    }

    //用户主动上分
    public function V8UserTopScores(Request $request){
        $money = $request->input("p");//要上分的金额
        $money = json_decode(aesDecrypt($money),true);
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        //调用上分
        $list = $this->V8log->V8UserTopScores($money,$data[0]);
        if(!$list){
            return [
                "code" => 404,
                "msg" => "link error",
            ];
        }
        return $list;
    }

    //用户主动下分
    public function V8UserLowerScores(Request $request){
        $money = $request->input("p");//要下分的金额
        $money = json_decode(aesDecrypt($money),true);
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        //调用下分
        $list = $this->V8log->V8UserLowerScores($money,$data[0]);
        if(!$list){
            return [
                "code" => 404,
                "msg" => "link error",
            ];
        }
        return $list;
    }

    //查询玩家V8总分
    public function V8QueryScore(Request $request){
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        //调用查询接口
        $list = $this->V8log->V8QueryScore($data[0]);
        if(!$list){
            return [
                "code" => 404,
                "msg" => "link error",
            ];
        }
        return $list;
    }


}
