<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WbetGetParlay extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'WbetGetParlay';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'wbet结算玩家投注球员拒绝订单';

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
        $url = $config["url"]."api/getparlaybetlist";
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
        $res = json_decode($res,true);
        if($res["status"] != "1"){
            echo "接口错误，联系接口提供方";
            exit();
        }
        if(empty($res["value"])){
            echo "没有新订单";
            exit();
        }
        //获取钱包
        $wallet = DB::table("wallet_name")->where("wallet_name",$config["game_name"])->select("id")->first();
        try {
            $result_list = [];
            foreach ($res["value"] as $k => $v){
                if($res["value"][$k]["bet_status"] == "Cancel" || $res["value"][$k]["bet_status"] == "Rejected"){
                    //用户退款金额
                    $money = $res["value"][$k]["bet_amount"];
                    $user = DB::table("users")->where("phone",$res["value"][$k]["member_id"])->select("id")->first();
                    //更新用户钱包
                    DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user->id])->increment("total_balance",$money,['withdrawal_balance'=>DB::raw("withdrawal_balance+$money")]);
                    $result_list[$k] = [
                        "a" => $res["value"][$k]["bet_id"]
                    ];
                }elseif($res["value"][$k]["bet_status"] == "Accepted"){
                    //用户退款金额
                    $money = $res["value"][$k]["bet_amount"];
                    $user = DB::table("users")->where("phone",$res["value"][$k]["member_id"])->select("id")->first();
                    //更新用户钱包
                    DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user->id])->decrement("total_balance",$money,['withdrawal_balance'=>DB::raw("withdrawal_balance-$money")]);
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
                $url = $config["url"]."api/markfetchparlaybetlist";
                //拼接验证码
                $ukey = mt_rand(00000000,99999999);
                $signature = md5($config["operator_id"].$ukey.$ukey.$config["Key"]);
                $params = [
                    "signature" => $signature,
                    "operator_id" => $config["operator_id"],
                    "ukey" => $ukey,
                    "bet_list_parlay" => $result_list
                ];
                $params = json_encode($params);
                $res = $this->curl_post($url, $params);
                $res = json_decode($res,true);
                Log::channel('kidebug')->info('wbet-parlay-return',[$res]);
            }
            exit();
        }catch (\Exception $e){
            echo $e->getMessage();
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
