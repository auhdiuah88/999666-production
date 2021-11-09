<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WbetGetBet extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'WbetGetBet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'wbet结算玩家投注拒绝订单';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $config = config("game.wbet");
        $url = $config["url"]."api/getbetlist";
        //拼接验证码
        $ukey = mt_rand(00000000,99999999);
        $signature = md5($config["operator_id"].$ukey.$ukey.$config["Key"]);
        $params = [
            "signature" => $signature,
            "operator_id" => $config["operator_id"],
            "ukey" => $ukey,
        ];
        $params = json_encode($params);
        $res = $this->curl_post($url, $params);
//        Log::channel('kidebug')->info('wbet-bet-input',[$res]);
        $res = json_decode($res,true);
        if($res["status"] != "1"){
            exit();
        }
        if(empty($res["value"])){
            exit();
        }
        $data = [];
        //获取钱包
        $wallet = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        try {
            $result_list = [];
            foreach ($res["value"] as $k => $v){
                if($res["value"][$k]["bet_status"] == "Cancel" || $res["value"][$k]["bet_status"] == "Rejected"){
                    //用户退款金额
                    $money = $res["value"][$k]["actual_bet_amount"];
                    $user = DB::table("users")->where("phone",$res["value"][$k]["member_id"])->select("id")->first();
                    //更新用户钱包
                    DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user->id])->increment("total_balance",$money,['withdrawal_balance'=>DB::raw("withdrawal_balance+$money")]);
                    $result_list[$k] = [
                        "a" => $res["value"][$k]["bet_id"]
                    ];
                    $data["bet_id"] = $res["value"][$k]["bet_id"];
                    $data["actual_bet_amount"] = $money;
                    $data["status"] = $res["value"][$k]["bet_status"];
                    $order = DB::table("wbet_order")->where("bet_id",$res["value"][$k]["bet_id"])->select();
                    if(!$order){
                        DB::table("wbet_order")->insert($data);
                    }else{
                        DB::table("wbet_order")->where("bet_id",$res["value"][$k]["bet_id"])->update($data);
                    }
                }elseif($res["value"][$k]["bet_status"] == "Accepted"){
                    $order = DB::table("wbet_order")->where("bet_id",$res["value"][$k]["bet_id"])->select();
                    if(!$order){
                        $data["bet_id"] = $res["value"][$k]["bet_id"];
                        $data["actual_bet_amount"] = $money;
                        $data["status"] = $res["value"][$k]["bet_status"];
                        DB::table("wbet_order")->insert($data);
                    }else{
                        //用户退款金额
                        $money = $res["value"][$k]["actual_bet_amount"];
                        $user = DB::table("users")->where("phone",$res["value"][$k]["member_id"])->select("id")->first();
                        //更新用户钱包
                        DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user->id])->decrement("total_balance",$money,['withdrawal_balance'=>DB::raw("withdrawal_balance-$money")]);
                    }
                    $result_list[$k] = [
                        "a" => $res["value"][$k]["bet_id"]
                    ];
                }else{
                    $result_list[$k] = [
                        "a" => $res["value"][$k]["bet_id"]
                    ];
                }
            }
            if(!empty($result_list)){
                //发送已更新订单
                $url = $config["url"]."api/markfetchbetlist";
                //拼接验证码
                $ukey = mt_rand(00000000,99999999);
                $signature = md5($config["operator_id"].$ukey.$ukey.$config["Key"]);
                $params = [
                    "signature" => $signature,
                    "operator_id" => $config["operator_id"],
                    "ukey" => $ukey,
                    "bet_list" => $result_list
                ];
                $params = json_encode($params);
                $res = $this->curl_post($url, $params);
//                Log::channel('kidebug')->info('wbet-bet-return',[$params]);
            }
            exit();
        }catch (\Exception $e){
            Log::channel('kidebug')->info('wbet-bet-error',[$e->getMessage()]);
            exit();
        }
    }

    //post请求
    public function curl_post($url , $data=array(),$header=[]){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if(!empty($header)){
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;

    }
}
