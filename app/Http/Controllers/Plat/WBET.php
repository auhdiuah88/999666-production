<?php
namespace App\Http\Controllers\Plat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Libs\Games\WBET\WbetLog;
use Illuminate\Support\Facades\Crypt;

class WBET extends Controller{
    private $Wbet;

    public function __construct
    (
        WbetLog $Wbet
    )
    {
        $this->Wbet = $Wbet;
    }

    //wbet平台用户入金
    public function WBETUserTopScores(Request $request){
        $money = $request->input("p");//要上分的金额
        $money = json_decode(aesDecrypt($money),true);
        $money = $money["money"];
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));

        $list = $this->Wbet->WBETUserTopScores($data[0],$money);
        if(!$list){
            return [
                "code" => 404,
                "msg" => "link error",
            ];
        }
        return $list;
    }

    //wbet平台用户出金
    public function WBETUserLowerScores(Request $request){
        $money = $request->input("p");//要上分的金额
        $money = json_decode(aesDecrypt($money),true);
        $money = $money["money"];
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        $list = $this->Wbet->WBETUserLowerScores($data[0],$money);
        if(!$list){
            return [
                "code" => 404,
                "msg" => "link error",
            ];
        }
        return $list;
    }

    //wbet平台查询用户钱包余额
    public function WBETQueryScore(Request $request){
        //获取用户ID
        $token = $request->header('token');
        $token = urldecode($token);
        $data = explode("+", Crypt::decrypt($token));
        //调用查询接口
        $list = $this->Wbet->WBETQueryScore($data[0]);
        if(!$list){
            return [
                "code" => 404,
                "msg" => "link error",
            ];
        }
        return $list;
    }

    //wbet平台获取用户余额-回调接口
    public function get_balance(Request $request){
        $res = $request->input();
        $config = config("game.wbet");
        //没有用户名
        $user = DB::table("users")->where("phone",$res["account_id"])->select()->first();
        if(!$res["account_id"] || !$user){
            return [
                "status" => 0,
                "statusdesc" => "accountIdRequired",
                "balance" => "0.00"
            ];
        }
        //没有代理
        if($res["operator_id"] != $config["operator_id"]){
            return [
                "status" => 0,
                "statusdesc" => "accountIdRequired",
                "balance" => "0.00"
            ];
        }
        //signature不匹配
        $signature = md5($config["operator_id"].$res["ukey"].$res["account_id"].$res["ukey"].$config["Key"]);
        if($res["signature"] != $signature){
            return [
                "status" => 0,
                "statusdesc" => "signatureRequired",
                "balance" => "0.00"
            ];
        }
        //获取玩家钱包余额
        $wallet_name = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet_name->id,"user_id" => $user->id])->select("withdrawal_balance")->first();
        return [
            "status" => 1,
            "statusdesc" => "ok",
            "balance" => $user_wallet->withdrawal_balance
        ];
    }

    //wbet平台用户下注
    public function bet(Request $request){
        $res = $request->input();
        $config = config("game.wbet");
        //没有用户名
        $user = DB::table("users")->where("phone",$res["account_id"])->select()->first();
        if(!$res["account_id"] || !$user){
            return [
                "status" => 0,
                "statusdesc" => "accountIdRequired",
                "balance" => "0.00"
            ];
        }
        //没有代理
        if($res["operator_id"] != $config["operator_id"]){
            return [
                "status" => 0,
                "statusdesc" => "accountIdRequired",
                "balance" => "0.00"
            ];
        }
        //signature不匹配
        $signature = md5($config["operator_id"].$res["ukey"].$res["account_id"].$res["ukey"].$config["Key"]);
        if($res["signature"] != $signature){
            return [
                "status" => 0,
                "statusdesc" => "signatureRequired",
                "balance" => "0.00"
            ];
        }
        //获取钱包名称
        $wallet = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        //获取用户钱包余额
        $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user->id])->select()->first();
        if($user_wallet->withdrawal_balance < $res["amount"]){
            return [
                "status" => 0,
                "statusdesc" => "insufficientBalance",
                "balance" => "0.00"
            ];
        }
        //创建转账订单
        $order = [
            "user_id" => $user->id,
            "order" => $res["ticket_id"],
            "wallet_id" => $wallet->id,
            "transfer_amount" => $res["amount"],
            "remaining_amount" => $user_wallet->withdrawal_balance - $res["amount"],
            "create_time" => time(),
            "remarks" => "wbet投注扣款"
        ];
        $order_id = DB::table("order")->insertGetId($order);
        try {
            //更新用户钱包
            DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user->id])->decrement("total_balance",$res["amount"],['withdrawal_balance'=>DB::raw("withdrawal_balance-".$res["amount"])]);
            //更新订单
            DB::table("order")->where("id",$order_id)->update(["status"=>1]);
            return [
                "status" => 1,
                "statusdesc" => "ok",
            ];
        }catch (\Exception $e){
            return [
                "status" => 0,
                "statusdesc" => "systemError",
                "balance" => "0.00"
            ];
        }
    }

    //wbet平台用户退款
    public function refund(Request $request){
        $res = $request->input();
        $config = config("game.wbet");
        //没有用户名
        $user = DB::table("users")->where("phone",$res["account_id"])->select()->first();
        if(!$res["account_id"] || !$user){
            return [
                "status" => 0,
                "statusdesc" => "accountIdRequired",
                "balance" => "0.00"
            ];
        }
        //没有代理
        if($res["operator_id"] != $config["operator_id"]){
            return [
                "status" => 0,
                "statusdesc" => "accountIdRequired",
                "balance" => "0.00"
            ];
        }
        //signature不匹配
        $signature = md5($config["operator_id"].$res["ukey"].$res["account_id"].$res["ukey"].$config["Key"]);
        if($res["signature"] != $signature){
            return [
                "status" => 0,
                "statusdesc" => "signatureRequired",
                "balance" => "0.00"
            ];
        }
        //订单是否存在
        $order = DB::table("order")->where("order",$res["ticket_id"])->select();
        if(!$order){
            return [
                "status" => 0,
                "statusdesc" => "noticket_id",
                "balance" => "0.00"
            ];
        }
        //获取钱包名称
        $wallet = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        //退款到钱包余额
        try {
            //更新用户wbet钱包
            DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user->id])->increment("total_balance",$res["amount"],['withdrawal_balance'=>DB::raw("withdrawal_balance+".$res["amount"])]);
            return [
                "status" => 1,
                "statusdesc" => "ok",
            ];
        }catch (\Exception $e){
            return [
                "status" => 0,
                "statusdesc" => "systemError",
                "balance" => "0.00"
            ];
        }
    }

}
