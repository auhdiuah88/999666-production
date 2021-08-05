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
        $config = config("game.pg");
        //获取用户数据
        $user_id = getUserIdFromToken(getToken());
        $info = DB::table('users')->where("id",$user_id)->select("phone","balance","ip","token")->first();
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
        //判断是否登录大厅
        if ($productId != "pg"){
            //拼接游戏url
            $url = $config["PgSoftPublicDomain"].$productId."/index.html?bet_type=1&operator_token=".$config["operator_token"]."&operator_player_session=".$info->token;
        }else{
            //拼接大厅url
            $url = $config["PgSoftPublicDomain"]."web-lobby/games/?operator_token=".$config["operator_token"]."&operator_player_session=".$info->token;
        }
        return $this->_data = [
            "url" => $url,
            "wallet" => $user_wallet->withdrawal_balance
        ];
    }

    //给PG平台发送查询用户余额
    public function PgQueryScore($user_id){
        $config = config("game.pg");
        //获取用户数据
        $info = DB::table('users')->where("id",$user_id)->select("phone","balance","ip")->first();
        if(empty($info)){
            return [
                "code" => 2,
                "msg" => "用户不存在",
                "data" => "",
            ];
        }
        $trace_id = $this->guid();
        //拼接url
        $url = $config["PgSoftAPIDomain"]."Cash/v3/GetPlayerWallet?trace_id=".$trace_id;
        //请求数据
        $param = [
            "operator_token" => $config["operator_token"],
            "secret_key" => $config["secret_key"],
            "player_name" => $info->phone,
        ];
        try {
            $res = $this->curl_post($url,$param);
            Log::channel('kidebug')->info('pg-PgQueryScore-return',[$res]);
            $res = json_decode($res,true);
            if($res["data"] != "null"){
                //更新用户钱包余额
                $wallet = $this->updateUserWallet($user_id,$res["data"]["cashBalance"]);
                if($wallet){
                    return $this->_data = $res["data"]["cashBalance"];
                }else{
                    return [
                        "code" => "4",
                        "meg" => "update error",
                        "data" => ""
                    ];
                }
            }else{
                return [
                    "code" => $res["error"]["code"],
                    "msg" => $res["error"]["message"],
                    "data" => ""
                ];
            }
        }catch (\Exception $e){
            return [
                "code" => 3,
                "msg" => $e->getMessage(),
                "data" => ""
            ];
        }
    }

    //更新用户钱包
    public function updateUserWallet($user_id,$balance){
        $config = config("game.pg");
        //获取用户钱包
        $wallet_name = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        $user_data = [
            "user_id" => $user_id,//用户ID
            "wallet_id" => $wallet_name->id,//游戏平台id
            "total_balance" => $balance,//用户总余额
            "withdrawal_balance" => $balance,//用户可下分余额
            "update_time" => time(),//更新时间
        ];
        $res = DB::table("users_wallet")->where(["wallet_id" => $wallet_name->id,"user_id" => $user_id])->update($user_data);
        return $res;
    }

    //上分
    public function PGUserTopScores($user_id,$money){
        $config = config("game.pg");
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
            "create_time" => time(),
            "remarks" => "icg上分钱包"
        ];
        $order_id = DB::table("order")->insertGetId($order);
        try {
            $trace_id = $this->guid();
            $url = $config["PgSoftAPIDomain"]."Cash/v3/TransferIn?trace_id=".$trace_id;
            $params = [
                "operator_token" => $config["operator_token"],
                "secret_key" => $config["secret_key"],
                "player_name" => $user->phone,
                "amount" => $money * 1000,
                "transfer_reference" => $create_time,
                "currency" => "VDN"
            ];
            $res = $this->curl_post($url, $params);
            Log::channel('kidebug')->info('pg-PGUserTopScores-return',[$res]);
            $res = json_decode($res,true);
            if(!empty($res["data"])){
                //更新订单
                DB::table("order")->where("id",$order_id)->update(["status" => "1"]);
                //更新用户余额
                DB::table("users")->where("id",$user_id)->update(["balance" => $user->balance - $money]);
                //更新用户钱包
                $this->PgQueryScore($user_id);
                return [
                    "code" => 200,
                    "msg" => "success",
                    "data" => $res["data"]["balanceAmount"],
                ];
            }else{
                throw new \Exception($res["error"]["message"]);
            }
        }catch (\Exception $e){
            return [
                "code" => 3,
                "msg" => $e->getMessage(),
                "data" => ""
            ];
        }
    }

    //生成GUID
    function guid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = chr(123)// "{"
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);// "}"
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
