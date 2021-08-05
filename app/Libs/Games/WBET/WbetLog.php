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
        $config = config("game.wbet");
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
        //创建用户,
        $this->CreateNewPlayer($info->phone);

        //获取游戏链接
        $game_link = $this->GameLink($info->phone);

        return $this->_data = [
            "url" => $game_link,
            "wallet" => $user_wallet["withdrawal_balance"]
        ];
    }

    //创建用户
    public function CreateNewPlayer($phone){
        //创建用户
        $config = config("game.wbet");
        $url = $config["url"]."api/createmember";
        //拼接验证码
        $ukey = mt_rand(00000000,99999999);
        $signature = md5($config["operator_id"].$ukey.$phone.$ukey.$config["Key"]);
        $params = [
            "signature" => $signature,
            "account_id" => $phone,
            "operator_id" => $config["operator_id"],
            "ukey" => $ukey,
        ];
        $params = json_encode($params);
        Log::channel('kidebug')->info('wbet-userlog-return',[$params]);
        $res = $this->curl_post($url, $params);
        Log::channel('kidebug')->info('wbet-userlog-return',[$res]);
        $res = json_decode($res,true);
        return $this->_data = $res;
    }

    //获取游戏链接
    public function GameLink($phone){
        $config = config("game.wbet");
        $url = $config["url"]."api/launchsports";
        //拼接验证码
        $ukey = mt_rand(00000000,99999999);
        $signature = md5($config["operator_id"].$ukey.$phone.$ukey.$config["Key"]);
        $params = [
            "signature" => $signature,
            "account_id" => $phone,
            "operator_id" => $config["operator_id"],
            "ukey" => $ukey,
            "lang" => "vi",
            "type" => "2",
        ];
        $params = json_encode($params);
        Log::channel('kidebug')->info('wbet-userlog-return',[$params]);
        $res = $this->curl_post($url, $params);
        Log::channel('kidebug')->info('wbet-userlog-return',[$res]);
        $res = json_decode($res,true);
        return $this->_data = $res["url"];
    }

    //用户上分
    public function WBETUserTopScores($user_id,$money){
        $config = config("game.wbet");
        //获取钱包
        $wallet = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        //获取剩余金额
        $user = DB::table("users")->where("id",$user_id)->select("balance","phone")->first();
        if (!$user){
            return [
                "code" => 1,
                "msg" => "用户不存在",
                "data" => ""
            ];
        }
        if ($user->balance < $money){
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
            "remaining_amount" => $user->balance - $money,
            "status" => "1",
            "create_time" => time(),
            "remarks" => "wbet上分钱包"
        ];
        DB::table("order")->insert($order);
        try {
            //更新用户余额
            DB::table("users")->where("id",$user_id)->update(["balance" => $user->balance - $money]);
            //更新用户钱包
            DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user_id])->increment("total_balance",$money,['withdrawal_balance'=>DB::raw("withdrawal_balance+$money")]);
            $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user_id])->select("withdrawal_balance")->first();
            return [
                "code" => 200,
                "msg" => "success",
                "data" => $user_wallet["withdrawal_balance"],
            ];
        }catch (\Exception $e){
            return [
                "code" => 3,
                "msg" => $e->getMessage(),
                "data" => ""
            ];
        }

    }

    //用户下分
    public function WBETUserLowerScores($user_id,$money){
        $config = config("game.wbet");
        //获取钱包
        $wallet = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        //获取剩余金额
        $user = DB::table("users")->where("id",$user_id)->select("balance","phone")->first();
        $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user_id])->select("withdrawal_balance")->first();
        if (!$user){
            return [
                "code" => 1,
                "msg" => "用户不存在",
                "data" => ""
            ];
        }
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
            "remaining_amount" => $user->balance + $money,
            "status" => "1",
            "create_time" => time(),
            "remarks" => "wbet下分钱包"
        ];
        DB::table("order")->insert($order);
        try {
            //更新用户余额
            DB::table("users")->where("id",$user_id)->update(["balance" => $user->balance + $money]);
            //更新用户钱包
            DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user_id])->decrement("total_balance",$money,['withdrawal_balance'=>DB::raw("withdrawal_balance-$money")]);
            //重新查询余额
            $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user_id])->select("withdrawal_balance")->first();
            return [
                "code" => 200,
                "msg" => "success",
                "data" => $user_wallet["withdrawal_balance"],
            ];
        }catch (\Exception $e){
            return [
                "code" => 3,
                "msg" => $e->getMessage(),
                "data" => ""
            ];
        }

    }

    //查询用户钱包余额
    public function WBETQueryScore($user_id){
        $config = config("game.wbet");
        //获取钱包
        $wallet = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        //获取剩余金额
        $user = DB::table("users")->where("id",$user_id)->select("balance","phone")->first();
        $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user_id])->select("withdrawal_balance")->first();
        if (!$user){
            return [
                "code" => 1,
                "msg" => "用户不存在",
                "data" => ""
            ];
        }
        return [
            "code" => "200",
            "msg" => "success",
            "data" => $user_wallet["withdrawal_balance"]
        ];
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
