<?php


namespace App\Libs\Games\WDYY;

use App\Libs\Games\GameStrategy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

require_once 'utils.php';
require_once 'config.php';
class Client extends GameStrategy
{

    public $_msg = "";
    public $_data = "";

    public function launch($productId)
    {
        $api = 'launch/mobile';
        $params = [
            'spId' => env('WDYY_SP_ID','68tbs'),
            'productId' => $productId,
            'returnUrl' => env('SHARE_URL',''),
            'token' => getToken(),
            'requestTime' => date('YmdHis'),
            'storeUrl' => trim(env('APP_URL',''),'/') . '/plat/bl-balance',
        ];
        $params['sign'] = generateSign($params);
        ##请求
        $params_string = json_encode($params);
        $header[] = "Content-Type: application/json";
        $header[] = "Content-Length: " . strlen($params_string);
        Log::channel('plat')->info('wdyy-launch-param',$params);
        $res = $this->doRequest(HOST . $api, $params_string, $header);
        Log::channel('plat')->info('wdyy-launch-return',[$res]);
        $res = json_decode($res,true);
        if(!$res){
            $this->_msg = 'launch request fail';
            return false;
        }
        if($res['retCode'] != '0'){
            $this->_msg = 'launch request fail .';
            return false;
        }
        $this->_data = $res['data']['gameUrl'];
        return true;
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

    public function balanceHandle(): bool
    {
        try{
            ##验签
            $params = checkSign();
            if($params === false)
            {
                throw new \Exception('验签失败');
            }
            ##减少用户余额
            $user_id = getUserIdFromToken($params['token']);
            if(!$this->updateBalance($params,$user_id,ACTION[$params['action']]))
            {
                throw new \Exception($this->_msg);
            }
            return true;
        }catch(\Exception $e){
            $this->_msg = $e->getMessage();
            $this->_data = [
                'retCode' => 1,
                'data' => []
            ];
            return false;
        }
    }

    protected function updateBalance($params, $user_id, $type)
    {
        DB::beginTransaction();
        try{
            $info = DB::table('users')->where("id",$user_id)->select("phone","code","balance")->lockForUpdate()->first();
            if(!$info)
            {
                throw new \Exception('用户不存在');
            }
            $balance = $info->balance;
            switch($type){
                case 101:  //betting
                    $money = $params['amt'] / 100;
                    if($balance < $money)
                    {
                        throw new \Exception('用户余额不足');
                    }
                    $wc_balance = bcsub($balance, $money);
                    $msg = sprintf('action:%s,betId:%d,gameName:%s,gameNumber:%s',$params['action'],$params['betId'],$params['gameName'],$params['gameNumber']);
                    break;
                case 102:  //win
                    $amt = $params['betAmt']??$params['amt'];
                    $money = $amt / 100;
                    $wc_balance = bcadd($balance, $money);
                    $msg = sprintf('action:%s,payoutId:%s,betId:%d,amt:%s,result:%s',$params['action'],$params['payoutId'],$params['betId'],$params['amt'],$params['result']);
                    break;
                case 103:  //betting退回
                    $amt = $params['amt'];
                    $money = $amt / 100;
                    $wc_balance = bcadd($balance, $money);
                    $msg = sprintf('action:%s,betId:%d,amt:%s',$params['action'],$params['betId'],$params['amt']);
                    break;
                case 104:  //win退回
                    $amt = $params['amt'];
                    $money = $amt / 100;
                    if($balance < $money)
                    {
                        throw new \Exception('用户余额不足');
                    }
                    $wc_balance = bcsub($balance, $money);
                    $msg = sprintf('action:%s,payoutId:%d,amt:%s',$params['action'],$params['payoutId'],$params['amt']);
                    break;
                default:
                    throw new \Exception('available type');
            }

            $balance_log_data = [
                'user_id' => $user_id,
                'type' => $type,
                'dq_balance' => $info->balance,
                'wc_balance' => $wc_balance,
                'time' => time(),
                'msg' => $msg,
                'money' => $money
            ];
            $res = DB::table('user_balance_logs')->insert($balance_log_data);
            if($res === false)
            {
                throw new \Exception('增加余额变更记录失败');
            }
            ##更新用户余额
            $res2 = DB::table('users')->where("id",$user_id)->update(['balance', $wc_balance]);
            if($res2 === false)
            {
                throw new \Exception('变更用户余额失败');
            }
            $this->_data = [
                'retCode' => 0,
                'data' => [
                    'balance' => $wc_balance * 100
                ]
            ];
            DB::commit();
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            $this->_msg = $e->getMessage();
            return false;
        }
    }

}
