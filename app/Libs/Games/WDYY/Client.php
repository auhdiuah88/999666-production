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
        //判断用户是否拥有钱包
        $user_id = getUserIdFromToken(getToken());
        $wallet_name = DB::table("wallet_name")->where("wallet_name","wdyy")->select("id")->first();
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
        return $this->_data = [
            "url" => $res['data']['gameUrl'],
            "wallet" => $user_wallet->withdrawal_balance,
            "game_id" => $wallet_name->id,
        ];
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
        //获取用户钱包
        $wallet_name = DB::table("wallet_name")->where("wallet_name","wdyy")->select("id")->first();
        $users_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet_name->id,"user_id" => $user_id])->select()->first();
        $data = [
            'account' => (string)$info->phone,
            'name' => $info->code,
            'balance' => $users_wallet->withdrawal_balance * 100,
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
//            $balance = $info->balance;
            $betting = 0;
            //获取用户钱包
            $wallet_name = DB::table("wallet_name")->where("wallet_name","wdyy")->select("id")->first();
            $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet_name->id,"user_id" => $user_id])->select("withdrawal_balance")->first();
            $wallet_balance = $user_wallet->withdrawal_balance;
            switch($type){
                case 101:  //betting
                    $money = $params['amt'] / 100;
                    if($wallet_balance < $money)
                    {
                        throw new \Exception('用户余额不足');
                    }
                    $wc_balance = bcsub($wallet_balance, $money);
                    $msg = sprintf('action:%s,betId:%d,gameName:%s,gameNumber:%s',$params['action'],$params['betId'],$params['gameName'],$params['gameNumber']);
                    $betting = $money;
                    break;
                case 102:  //win
                    $amt = $params['betAmt']??$params['amt'];
                    $money = $amt / 100;
                    $wc_balance = bcadd($wallet_balance, $money);
                    $msg = sprintf('action:%s,payoutId:%s,betId:%d,amt:%s,result:%s',$params['action'],$params['payoutId'],$params['betId'],$params['amt'],$params['result']);
                    break;
                case 103:  //betting退回
                    $amt = $params['amt'];
                    $money = $amt / 100;
                    $wc_balance = bcadd($wallet_balance, $money);
                    $msg = sprintf('action:%s,betId:%d,amt:%s',$params['action'],$params['betId'],$params['amt']);
                    break;
                case 104:  //win退回
                    $amt = $params['amt'];
                    $money = $amt / 100;
                    if($wallet_balance < $money)
                    {
                        throw new \Exception('用户余额不足');
                    }
                    $wc_balance = bcsub($wallet_balance, $money);
                    $msg = sprintf('action:%s,payoutId:%d,amt:%s',$params['action'],$params['payoutId'],$params['amt']);
                    break;
                default:
                    throw new \Exception('available type');
            }
            if($money <= 0)
            {
                $this->_data = [
                    'retCode' => 0,
                    'data' => [
                        'balance' => $wc_balance * 100
                    ]
                ];
                DB::rollBack();
                return true;
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
//            $res2 = DB::table('users')->where("id",$user_id)->update(['balance'=>$wc_balance]);
            //更新用户余额
            $res2 = DB::table("users_wallet")->where(["wallet_id" => $wallet_name->id,"user_id" => $user_id])->update(["total_balance" => $wc_balance,"withdrawal_balance"=>$wc_balance]);
            if($res2 === false)
            {
                throw new \Exception('变更用户余额失败');
            }
            if($betting > 0)
            {
                $this->addUserBetting($user_id, $betting);
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

    //用户上分
    public function TopScores($money,$user_id){
        //获取钱包
        $wallet = DB::table("wallet_name")->where("wallet_name","wdyy")->select("id")->first();
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
            "status" => "1",
            "create_time" => time(),
            "remarks" => "wdyy上分钱包"
        ];
        DB::table("order")->insert($order);
        try {
            //更新用户余额
            DB::table("users")->where("id",$user_id)->update(["balance" => $user->balance - $money]);
            //更新用户钱包
            DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user_id])->increment("total_balance",$money,['withdrawal_balance'=>DB::raw("withdrawal_balance+$money")]);
            $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user_id])->select("withdrawal_balance")->first();
            return $this->_data = sprintf('%01.2f',$user_wallet->withdrawal_balance);
        }catch (\Exception $e){
            return $this->_msg = $e->getMessage();
        }

    }

    //用户下分
    public function LowerScores($money,$user_id){
        //获取钱包
        $wallet = DB::table("wallet_name")->where("wallet_name","wdyy")->select("id")->first();
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
            "status" => "1",
            "create_time" => time(),
            "remarks" => "wdyy下分钱包"
        ];
        DB::table("order")->insert($order);
        try {
            //更新用户余额
            DB::table("users")->where("id",$user_id)->update(["balance" => $user->balance + $money]);
            //更新用户钱包
            DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user_id])->decrement("total_balance",$money,['withdrawal_balance'=>DB::raw("withdrawal_balance-$money")]);
            //重新查询余额
            $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user_id])->select("withdrawal_balance")->first();
            return $this->_data = sprintf('%01.2f',$user_wallet->withdrawal_balance);
        }catch (\Exception $e){
            return $this->_msg = $e->getMessage();
        }

    }

    //查询用户钱包余额
    public function QueryScore($user_id){
        //获取钱包
        $wallet = DB::table("wallet_name")->where("wallet_name","wdyy")->select("id")->first();
        //获取剩余金额
        $user = DB::table("users")->where("id",$user_id)->select("balance","phone")->first();
        $user_wallet = DB::table("users_wallet")->where(["wallet_id" => $wallet->id,"user_id" => $user_id])->select("withdrawal_balance")->first();
        if (!$user){
            return $this->_msg = "用户不存在";
        }
        return $this->_data = sprintf('%01.2f',$user_wallet->withdrawal_balance);
    }

}
