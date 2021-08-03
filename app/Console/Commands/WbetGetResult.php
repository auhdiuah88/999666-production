<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WbetGetResult extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'WbetGetResult';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'wbet结算玩家投注订单';

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
        $url = $config["url"]."api/getresult";
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
        print_r($res);
        exit();
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
