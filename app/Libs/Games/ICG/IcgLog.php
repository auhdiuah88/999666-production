<?php
namespace App\Libs\Games\ICG;

use App\Libs\Games\GameStrategy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IcgLog extends GameStrategy
{
    public $_msg = "";
    public $_data = "";

    //给ICG平台发送登录信息
    public function launch($productId){
        //获取用户数据
        $user_id = getUserIdFromToken(getToken());
        $info = DB::table('users')->where("id",$user_id)->select("phone","balance","ip")->first();
        if (!$info){
            return $this->_msg = "用户不存在";
        }
        $config = config("game.icg");
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
        //判断缓存中是否存在token
        if (cache('icgtoken')) {
            $token = cache("icgtoken");
        }else{
            //获取秘钥
            $gettoken = $this->GetToken();
            $token = $gettoken["token"];
            cache(["icgtoken" => $token]);
        }
        //创建玩家
        $getuser = $this->CreateNewPlayer($info->phone,$token);
        if($getuser == 2){
            //重新获取秘钥
            $gettoken = $this->GetToken();
            $token = $gettoken["token"];
            cache(["icgtoken" => $token]);
        }

        //获取游戏ID
//        $productId = $this->GameList($token);

        //获取游戏链接
        $game_link = $this->GameLink($info->phone,$token,$productId);

        return $this->_data = [
            "url" => $game_link,
            "wallet" => $user_wallet->withdrawal_balance,
            "game_name" => $productId,
        ];
    }

    //获取秘钥
    public function GetToken(){
        $config = config("game.icg");
        $url = $config["url"]."login";
        $params = [
            "username" => $config["username"],
            "password" => $config["password"],
        ];
        $res = $this->curl_post($url, $params);
        Log::channel('kidebug')->info('icg-GetToken-return',[$res]);
        $res = json_decode($res,true);
        return $res;
    }

    //创建新玩家
    public function CreateNewPlayer($user_name,$token){
        $config = config("game.icg");
        $url = $config["url"]."api/v1/players";
        $params = [
            "username" => $user_name,
        ];
        $header[] = "Authorization: Bearer ".$token;
        $res = $this->curl_post($url, $params,$header);
        Log::channel('kidebug')->info('icg-GetToken-return',[$res]);
        $res = json_decode($res,true);
        if(isset($res["error"]) && $res["error"]["status"] == "401"){
            return 2;
        }
        return 1;
    }

    //获取游戏链接
    public function GameLink($user_name,$token,$productId){
        $config = config("game.icg");
        $url = $config["url"]."api/v1/games/gamelink";
        $params = [
            "lang" => "en",
        ];
        $productId = $productId=="icg"?"lobby01":$productId;
        $url = $url."?lang=".$params["lang"]."&productId=".$productId."&player=".$user_name;
        $header[] = "Authorization: Bearer ".$token;
        $res = $this->GetCurl($url,$header);
        Log::channel('kidebug')->info('icg-GetToken-return',[$res]);
        $res = json_decode($res,true);
        return $res["data"]["url"];
    }

    //用户上分
    public function TopScores($money,$user_id){
        $config = config("game.icg");
        //获取钱包
        $wallet = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        //获取剩余金额
        $user = DB::table("users")->where("id",$user_id)->select("balance","phone")->first();
        if (!$user){
            return $this->_msg = "用户不存在";
        }
        if ($user->balance < $money){
            return $this->_msg = "余额不足";
        }
        //创建转账订单
        $create_time = time().rand("000","999");
        $order = [
            "user_id" => $user_id,
            "order" => $create_time,
            "wallet_id" => $wallet->id,
            "transfer_amount" => $money,
            "remaining_amount" => $user->balance - $money,
            "create_time" => time(),
            "remarks" => "icg上分钱包"
        ];

        //判断缓存中是否存在token
        if (cache('icgtoken')) {
            $token = cache("icgtoken");
        }else{
            //获取秘钥
            $gettoken = $this->GetToken();
            $token = $gettoken["token"];
            cache(["icgtoken" => $token]);
        }
        $order_id = DB::table("order")->insertGetId($order);
        try {
            $url = $config["url"]."api/v1/players/deposit";
            $params = [
                "transactionId" => $create_time,
                "amount" => $money * 100,
                "player" => $user->phone,
            ];
            $header[] = "Authorization: Bearer ".$token;
            $res = $this->curl_post($url, $params,$header);
            Log::channel('kidebug')->info('icg-TopScores-return',[$res]);
            $res = json_decode($res,true);
            if(isset($res["data"])){
                $balance_log_data = [
                    'user_id' => $user_id,
                    'type' => 2,
                    'dq_balance' => $user->balance,
                    'wc_balance' => $user->balance - $money,
                    'time' => time(),
                    'msg' => "icg钱包充值".sprintf('%01.2f',$money),
                    'money' => $money
                ];
                $log_data = DB::table('user_balance_logs')->insert($balance_log_data);
                if($log_data === false)
                {
                    throw new \Exception('增加余额变更记录失败');
                }
                //更新订单
                DB::table("order")->where("id",$order_id)->update(["status" => "1"]);
                //更新用户余额
                DB::table("users")->where("id",$user_id)->update(["balance" => $user->balance - $money]);
                //更新用户钱包
                $this->QueryScore($user_id);
                return $this->_data = sprintf('%01.2f',$res["data"]["balance"] / 100);
            }
        }catch (\Exception $e){
            return $this->_msg = $e->getMessage();
        }
    }

    //用户下分
    public function LowerScores($money,$user_id){
        $config = config("game.icg");
        //获取钱包
        $wallet = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        //获取剩余金额
        $user = DB::table("users")->where("id",$user_id)->select("balance","phone")->first();
        $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user_id])->select("withdrawal_balance")->first();
        if (!$user){
            return $this->_msg = "用户不存在";
        }
        if ($user_wallet->withdrawal_balance < $money){
            return $this->_msg = "余额不足";
        }
        //创建转账订单
        $create_time = time().rand("000","999");
        $order = [
            "user_id" => $user_id,
            "order" => $create_time,
            "wallet_id" => $wallet->id,
            "transfer_amount" => $money,
            "remaining_amount" => $user->balance + $money,
            "create_time" => time(),
            "remarks" => "icg下分钱包"
        ];
        $order_id = DB::table("order")->insertGetId($order);
        //判断缓存中是否存在token
        if (cache('icgtoken')) {
            $token = cache("icgtoken");
        }else{
            //获取秘钥
            $gettoken = $this->GetToken();
            $token = $gettoken["token"];
            cache(["icgtoken" => $token]);
        }
        try {
            $url = $config["url"]."api/v1/players/withdraw";
            $params = [
                "transactionId" => $create_time,
                "amount" => $money * 100,
                "player" => $user->phone,
            ];
            $header[] = "Authorization: Bearer ".$token;
            $res = $this->curl_post($url, $params,$header);
            Log::channel('kidebug')->info('icg-LowerScores-return',[$res]);
            $res = json_decode($res,true);
            if(isset($res["data"])){
                $balance_log_data = [
                    'user_id' => $user_id,
                    'type' => 2,
                    'dq_balance' => $user->balance,
                    'wc_balance' => $user->balance + $money,
                    'time' => time(),
                    'msg' => "icg钱包提现".sprintf('%01.2f',$money),
                    'money' => $money
                ];
                $log_data = DB::table('user_balance_logs')->insert($balance_log_data);
                if($log_data === false)
                {
                    throw new \Exception('增加余额变更记录失败');
                }
                //更新订单
                DB::table("order")->where("id",$order_id)->update(["status" => "1"]);
                //更新用户余额
                DB::table("users")->where("id",$user_id)->update(["balance" => $user->balance + $money]);
                //更新用户钱包
                $this->QueryScore($user_id);
                return $this->_data = sprintf('%01.2f',$res["data"]["balance"] / 100);
            }
        }catch (\Exception $e){
            return $this->_msg = $e->getMessage();
        }
    }

    //查询用户余额
    public function QueryScore($user_id){
        $config = config("game.icg");
        //获取用户信息
        $user = DB::table("users")->where("id",$user_id)->select("phone")->first();
        if (!$user){
            return $this->_msg = "用户不存在";
        }
        //判断缓存中是否存在token
        if (cache('icgtoken')) {
            $token = cache("icgtoken");
        }else{
            //获取秘钥
            $gettoken = $this->GetToken();
            $token = $gettoken["token"];
            cache(["icgtoken" => $token]);
        }
        //获取钱包
        $wallet_id = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        $url = $config["url"]."api/v1/players";
        $url = $url."?player=".$user->phone;
        $header[] = "Authorization: Bearer ".$token;
        try {
            $res = $this->GetCurl($url,$header);
            Log::channel('kidebug')->info('icg-QueryScore-return',[$res]);
            $res = json_decode($res,true);
            if (isset($res["data"])){
                $users_wallet = [
                    "user_id" => $user_id,
                    "wallet_id" => $wallet_id->id,
                    "total_balance" => $res["data"][0]["balance"] / 100,
                    "withdrawal_balance" => $res["data"][0]["balance"] / 100,
                    "update_time" => time(),
                ];
                //更新用户钱包
                $wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet_id->id,"user_id" => $user_id])->select()->first();
                if(!$wallet){
                    DB::table("users_wallet")->insert($users_wallet);
                }else{
                    DB::table("users_wallet")->where(["wallet_id" => $wallet_id->id,"user_id" => $user_id])->update($users_wallet);
                }
                return $this->_data = sprintf('%01.2f',$res["data"][0]["balance"] / 100);
            }
        }catch (\Exception $e){
            return $this->_msg = $e->getMessage();
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
