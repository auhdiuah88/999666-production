<?php
namespace App\Libs\Games\V8;

use App\Libs\Games\GameStrategy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Libs\Aes;

require_once 'config.php';
class V8log extends GameStrategy
{
    public $_msg = "";
    public $_data = "";

    //给V8平台发送登录信息
    public function launch($productId){
        //获取用户数据
        $user_id = getUserIdFromToken(getToken());
        $info = DB::table('users')->where("id",$user_id)->select("phone","balance","ip")->first();
        if(empty($info)){
            $this->_msg = '用户不存在';
            $this->_data = [
                'retCode' => 2,
                'data' => []
            ];
            return false;
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
            "money" => $info->balance,//金额
            "orderid" => V8AGENT.$datetime.$info->phone,//拼接agent,当前时间，用户名
            "ip" => $info->ip,
            "lineCode" => env("LINECODE"),
            "kid" => "0",//固定值，不需修改
        ];
        //加密$param
        $param = "s=".$param["s"]."&account=".$param["account"]."&money=".$param["money"]."&orderid=".$param["orderid"]."&ip=".$param["ip"]."&lineCode=".$param["lineCode"]."&KindID=".$param["kid"];
        Log::channel('kidebug')->info('v8',[$param]);
        $aes = new Aes();
        $param = $aes->encryptno64($param,V8DESKEY);

        //加密KEY
        $key = md5(V8AGENT.$timestamp.$milliseconds.M5KEY);

        //拼接URL
        $url = V8URL."?agent=".V8AGENT."&timestamp=".$timestamp.$milliseconds."&param=".$param."&key=".$key;
        Log::channel('kidebug')->info('v8',[$url]);
        //请求三方接口
//        $res = $this->GetCurl($url);
        $res = file_get_contents($url);
//        //请求返回日志
        Log::channel('kidebug')->info('v8',[$res]);
        $res = json_decode($res,true);
        $resurl = $res["d"]["url"];
        return $this->_data = $resurl;
    }

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
