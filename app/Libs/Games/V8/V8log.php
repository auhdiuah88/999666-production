<?php
namespace App\Libs\Games\V8;

use App\Libs\Games\GameStrategy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Libs\Aes;

class V8log extends GameStrategy
{
    public $_msg = "";
    public $_data = "";

    //给V8平台发送登录信息
    public function launch($productId){
        $config = config("game.v8");
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
        //判断用户是否拥有钱包
        $wallet_name = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        $user_data = [
            "user_id" => $user_id,//用户ID
            "wallet_id" => $wallet_name->id,//游戏平台id
            "total_balance" => 0,//用户总余额
            "withdrawal_balance" => 0,//用户可下分余额
            "update_time" => time(),//更新时间
        ];
        $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet_name->id,"user_id" => $user_id])->select("withdrawal_balance")->first();
        if(!$user_wallet){
            DB::table("users_wallet")->insert($user_data);
            $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet_name->id,"user_id" => $user_id])->select("withdrawal_balance")->first();
        }
        //获取当前时间（毫秒级）
        $mtimestamp = sprintf("%.3f", microtime(true)); // 带毫秒的时间戳
        $timestamp = floor($mtimestamp); // 时间戳
        $milliseconds = round(($mtimestamp - $timestamp) * 1000); // 毫秒
        $datetime = date("YmdHis", $timestamp) . $milliseconds;

        //拼接请求参数
        $param = [
            "s" => "0",//固定值，不需修改
            "account" => $info->phone,//用户名
            "money" => 0,//金额
            "orderid" => $config["agent"].$datetime.$info->phone,//拼接agent,当前时间，用户名
            "ip" => $info->ip,
            "lineCode" => env("LINECODE"),
            "kid" => $productId == "v8"?"0":$productId,//游戏ID，0为大厅
        ];

        //加密$param
        $param = "s=".$param["s"]."&account=".$param["account"]."&money=".$param["money"]."&orderid=".$param["orderid"]."&ip=".$param["ip"]."&lineCode=".$param["lineCode"]."&KindID=".$param["kid"];
        Log::channel('kidebug')->info('v8',[$param]);
        $aes = new Aes();
        //编码url
        $param = urlencode($aes->encryptno64($param,$config["deskey"]));

        //加密KEY
        $key = md5($config["agent"].$timestamp.$milliseconds.$config["md5key"]);

        //拼接URL
        $url = $config["url"]."?agent=".$config["agent"]."&timestamp=".$timestamp.$milliseconds."&param=".$param."&key=".$key;
        Log::channel('kidebug')->info('v8',[$url]);
        //请求三方接口
        try {
            $res = file_get_contents($url);
            //请求返回日志
            Log::channel('kidebug')->info('v8',[$res]);
            $res = json_decode($res,true);
            if($res["d"]["code"] != "0"){
                return [
                    "code" => 4,
                    "msg" => $res["m"],
                    "data" => "",
                ];
            }
            return $this->_data = [
                "url" => $res["d"]["url"],
                "wallet" => $user_wallet->withdrawal_balance
            ];
        }catch (\Exception $e){
            return [
                "code" => 3,
                "msg" => $e->getMessage(),
                "data" => "",
            ];
        }
    }

    //上分
    public function TopScores($money,$user_id){
        //获取用户数据
        $info = DB::table('users')->where("id",$user_id)->select("phone","balance","ip")->first();
        if(empty($info)){
            return [
                "code" => 2,
                "msg" => "用户不存在",
                "data" => "",
            ];
        }
        if($info->balance < $money){
            return [
                "code" => 2,
                "msg" => "用户余额不足",
                "data" => "",
            ];
        }


        $config = config("game.v8");

        //获取钱包
        $wallet = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        //创建转账订单
        $create_time = time().rand("000","999");
        $order = [
            "user_id" => $user_id,
            "order" => $create_time,
            "wallet_id" => $wallet->id,
            "transfer_amount" => $money,
            "remaining_amount" => $info->balance - $money,
            "create_time" => time(),
            "remarks" => "v8上分钱包"
        ];
        $order_id = DB::table("order")->insertGetId($order);

        //获取当前时间（毫秒级）
        $mtimestamp = sprintf("%.3f", microtime(true)); // 带毫秒的时间戳
        $timestamp = floor($mtimestamp); // 时间戳
        $milliseconds = round(($mtimestamp - $timestamp) * 1000); // 毫秒
        $datetime = date("YmdHis", $timestamp) . $milliseconds;

        //拼接请求参数
        $param = [
            "s" => "2",//固定值，不需修改
            "account" => $info->phone,//用户名
            "money" => $money,//金额
            "orderid" => $config["agent"].$datetime.$info->phone,//拼接agent,当前时间，用户名
        ];

        //加密$param
        $param = "s=".$param["s"]."&account=".$param["account"]."&money=".$param["money"]."&orderid=".$param["orderid"];
        Log::channel('kidebug')->info('v8',[$param]);
        $aes = new Aes();
        $param = urlencode($aes->encryptno64($param,$config["deskey"]));

        //加密KEY
        $key = md5($config["agent"].$timestamp.$milliseconds.$config["md5key"]);

        //拼接URL
        $url = $config["url"]."?agent=".$config["agent"]."&timestamp=".$timestamp.$milliseconds."&param=".$param."&key=".$key;
        Log::channel('kidebug')->info('v8',[$url]);
        //扣除金额并请求三方接口
        try {
            $res = file_get_contents($url);
            //请求返回日志
            Log::channel('kidebug')->info('v8',[$res]);
            $res = json_decode($res,true);
            if($res["d"]["code"] != "0"){
                return [
                    "code" => 4,
                    "msg" => $res["m"],
                    "data" => "",
                ];
            }
            //更新用户余额
            DB::table("users")->where("id",$user_id)->decrement("balance",$money);
            //更新订单
            DB::table("order")->where("id",$order_id)->update(["status" => "1"]);
            //查询用户总余额
            $reqmoney = $this->V8QueryScore($user_id);
            if($reqmoney["code"] != "200"){
                return [
                    "code" => 5,
                    "msg" => $reqmoney["msg"],
                    "data" => "",
                ];
            }
            return [
                "code" => 200,
                "msg" => "success",
                "data" => $res["d"]["money"],
            ];
        }catch (\Exception $e){
            return [
                "code" => 3,
                "msg" => $e->getMessage(),
                "data" => "",
            ];
        }
    }

    //下分
    public function LowerScores($money,$user_id){
        $config = config("game.v8");
        //获取用户数据
        $info = DB::table('users')->where("id",$user_id)->select("phone","balance","ip")->first();
        if(empty($info)){
            return [
                "code" => 2,
                "msg" => "用户不存在",
                "data" => "",
            ];
        }
        //获取钱包
        $wallet = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user_id])->select("withdrawal_balance")->first();
        if ($user_wallet->withdrawal_balance < $money){
            return [
                "code" => 2,
                "msg" => "余额不足",
                "data" => ""
            ];
        }

        //创建转账订单
        $create_time = time().rand("000","999");
        $order = [
            "user_id" => $user_id,
            "order" => $create_time,
            "wallet_id" => $wallet->id,
            "transfer_amount" => $money,
            "remaining_amount" => $info->balance + $money,
            "create_time" => time(),
            "remarks" => "v8下分钱包"
        ];
        $order_id = DB::table("order")->insertGetId($order);

        //获取当前时间（毫秒级）
        $mtimestamp = sprintf("%.3f", microtime(true)); // 带毫秒的时间戳
        $timestamp = floor($mtimestamp); // 时间戳
        $milliseconds = round(($mtimestamp - $timestamp) * 1000); // 毫秒
        $datetime = date("YmdHis", $timestamp) . $milliseconds;

        //拼接可下分余额请求参数
        $param = [
            "s" => "3",//固定值，不需修改
            "account" => $info->phone,//用户名
            "money" => $money,//金额
            "orderid" => $config["agent"].$datetime.$info->phone,//拼接agent,当前时间，用户名
        ];

        //加密$param
        $param = "s=".$param["s"]."&account=".$param["account"]."&money=".$param["money"]."&orderid=".$param["orderid"];
        Log::channel('kidebug')->info('v8',[$param]);
        $aes = new Aes();
        $param = urlencode($aes->encryptno64($param,$config["deskey"]));

        //加密KEY
        $key = md5($config["agent"].$timestamp.$milliseconds.$config["md5key"]);

        //拼接URL
        $url = $config["url"]."?agent=".$config["agent"]."&timestamp=".$timestamp.$milliseconds."&param=".$param."&key=".$key;
        Log::channel('kidebug')->info('v8',[$url]);
        try {
            $res = file_get_contents($url);
            //请求返回日志
            Log::channel('kidebug')->info('v8',[$res]);
            $res = json_decode($res,true);
            if($res["d"]["code"] != "0"){
                return [
                    "code" => 4,
                    "msg" => $res["m"],
                    "data" => "",
                ];
            }
            //更新用户余额
            DB::table("users")->where("id",$user_id)->increment("balance",$money);
            //更新订单
            DB::table("order")->where("id",$order_id)->update(["status" => "1"]);
            //更新用户钱包余额
            $reqmoney = $this->V8QueryScore($user_id);
            if($reqmoney["code"] != "200"){
                return [
                    "code" => 5,
                    "msg" => $reqmoney["msg"],
                    "data" => "",
                ];
            }

            return [
                "code" => 200,
                "msg" => "success",
                "data" => $res["d"]["money"],
            ];
        }catch (\Exception $e){
            return [
                "code" => 3,
                "msg" => $e->getMessage(),
                "data" => "",
            ];
        }
    }

    //查询用户余额
    public function QueryScore($user_id){
        //获取用户数据
        $info = DB::table('users')->where("id",$user_id)->select("phone","balance","ip")->first();
        if(empty($info)){
            return [
                "code" => 2,
                "msg" => "用户不存在",
                "data" => "",
            ];
        }

        $config = config("game.v8");
        //获取当前时间（毫秒级）
        $mtimestamp = sprintf("%.3f", microtime(true)); // 带毫秒的时间戳
        $timestamp = floor($mtimestamp); // 时间戳
        $milliseconds = round(($mtimestamp - $timestamp) * 1000); // 毫秒

        //拼接可下分余额请求参数
        $param = [
            "s" => "7",//固定值，不需修改
            "account" => $info->phone,//用户名
        ];

        //加密$param
        $param = "s=".$param["s"]."&account=".$param["account"];
        Log::channel('kidebug')->info('v8',[$param]);
        $aes = new Aes();
        $param = urlencode($aes->encryptno64($param,$config["deskey"]));

        //加密KEY
        $key = md5($config["agent"].$timestamp.$milliseconds.$config["md5key"]);

        //拼接URL
        $url = $config["url"]."?agent=".$config["agent"]."&timestamp=".$timestamp.$milliseconds."&param=".$param."&key=".$key;
        Log::channel('kidebug')->info('v8',[$url]);
        try {
            $res = file_get_contents($url);
            //请求返回日志
            Log::channel('kidebug')->info('v8',[$res]);
            $res = json_decode($res,true);
            if($res["d"]["code"] != "0"){
                return [
                    "code" => 4,
                    "msg" => $res["m"],
                    "data" => "",
                ];
            }
            $wallet_name = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
            $user_data = [
                "user_id" => $user_id,//用户ID
                "wallet_id" => $wallet_name->id,//游戏平台id
                "total_balance" => $res["d"]["totalMoney"],//用户总余额
                "withdrawal_balance" => $res["d"]["freeMoney"],//用户可下分余额
                "update_time" => time(),//更新时间
            ];
            $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet_name->id,"user_id" => $user_id])->get();
            if(!$user_wallet){
                DB::table("users_wallet")->insert($user_data);
            }else{
                DB::table("users_wallet")->where(["wallet_id" => $wallet_name->id,"user_id" => $user_id])->update($user_data);
            }
            return [
                "code" => 200,
                "msg" => "success",
                "data" => [
                    "totalMoney" => $res["d"]["totalMoney"],//用户总余额
                    "freeMoney" => $res["d"]["freeMoney"],//用户可下分余额
                ],
            ];
        }catch (\Exception $e){
            return [
                "code" => 3,
                "msg" => $e->getMessage(),
                "data" => "",
            ];
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
