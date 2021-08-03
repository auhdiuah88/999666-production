<?php


namespace App\Repositories\Admin;


use App\Libs\DRedis;
use App\Models\Cx_Charge_Logs;
use App\Models\Cx_Game_Betting;
use App\Models\Cx_Sign_Order;
use App\Models\Cx_User;
use App\Models\Cx_User_Balance_Logs;
use App\Models\Cx_User_Recharge_Logs;
use App\Models\Cx_Withdrawal_Record;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Predis\Client;

class HomeRepository extends BaseRepository
{
    private
        $Cx_User,
        $Cx_User_Balance_Logs,
        $Cx_Withdrawal_Record,
        $Cx_Charge_Logs,
        $Cx_Game_Betting,
        $Cx_Sign_Orders,
        $Cx_User_Recharge_Logs;

    public function __construct(
        Cx_User $cx_User,
        Cx_User_Balance_Logs $balance_Logs,
        Cx_Withdrawal_Record $cx_Withdrawal_Record,
        Cx_Charge_Logs $charge_Logs,
        Cx_Game_Betting $game_Betting,
        Cx_Sign_Order $cx_Sign_Order,
        Cx_User_Recharge_Logs $cx_User_Recharge_Logs
    )
    {
        $this->Cx_User = $cx_User;
        $this->Cx_User_Balance_Logs = $balance_Logs;
        $this->Cx_Withdrawal_Record = $cx_Withdrawal_Record;
        $this->Cx_Charge_Logs = $charge_Logs;
        $this->Cx_Game_Betting = $game_Betting;
        $this->Cx_Sign_Orders = $cx_Sign_Order;
        $this->Cx_User_Recharge_Logs = $cx_User_Recharge_Logs;
    }

    public function findAll($offset, $limit)
    {
        return $this->Cx_User->select(["id", "nickname", "phone", "balance"])->offset($offset)->limit($limit)->get();
    }

    public function countAll()
    {
        return $this->Cx_User->where("is_customer_service", 1)->count("id");
    }

    public function sumGiveMoney($reg_source_id, $timeMap)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')->leftJoin('users as u', 'ubl.user_id', '=','u.id')->where("u.reg_source_id",'=',$reg_source_id)->where("ubl.type", 5)->whereBetween("ubl.time", $timeMap)->sum("ubl.money");
        }else{
            return $this->Cx_User_Balance_Logs->where("type", 5)->whereBetween("time", $timeMap)->sum("money");
        }
    }

    public function sumRechargeRebate($reg_source_id, $timeMap)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')->leftJoin('users as u', 'ubl.user_id', '=','u.id')->where("u.reg_source_id",'=',$reg_source_id)->where("ubl.type", 14)->whereBetween("ubl.time", $timeMap)->sum("ubl.money");
        }else{
            return $this->Cx_User_Balance_Logs->where("type", 14)->whereBetween("time", $timeMap)->sum("money");
        }
    }

    public function sumRegisterRebate($reg_source_id, $timeMap)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')->leftJoin('users as u', 'ubl.user_id', '=','u.id')->where("u.reg_source_id",'=',$reg_source_id)->where("ubl.type", 15)->whereBetween("ubl.time", $timeMap)->sum("ubl.money");
        }else{
            return $this->Cx_User_Balance_Logs->where("type", 15)->whereBetween("time", $timeMap)->sum("money");
        }
    }

    public function countNewMembers($timeMap, $reg_source_id)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User->where("reg_source_id","=",$reg_source_id)->whereBetween("reg_time", $timeMap)->count("id");
        }else{
            return $this->Cx_User->whereBetween("reg_time", $timeMap)->count("id");
        }
    }

    public function countMembers($reg_source_id)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User->where("reg_source_id",'=',$reg_source_id)->count("id");
        }else{
            return $this->Cx_User->count("id");
        }
    }

    public function countRechargeAgainUserNumber($reg_source_id)
    {
        $prefix = DB::getConfig('prefix');
        if($reg_source_id >= 0){
            $list = DB::select("select count(url.id) as recharge_num FROM ".$prefix."user_recharge_logs url LEFT JOIN ".$prefix."users u ON u.id = url.user_id WHERE url.status = 2 and u.reg_source_id = ".$reg_source_id." GROUP BY url.user_id HAVING recharge_num > 1");
        }else{
            $list = DB::select("select count(url.id) as recharge_num FROM ".$prefix."user_recharge_logs url WHERE url.status = 2 GROUP BY url.user_id HAVING recharge_num > 1");
        }
        return count($list);
    }

    public function countOrdinaryMembers($timeMap, $reg_source_id)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User->where("reg_source_id",'=',$reg_source_id)->whereBetween("reg_time", $timeMap)->whereNull("two_recommend_id")->count("id");
        }else{
            return $this->Cx_User->whereBetween("reg_time", $timeMap)->whereNull("two_recommend_id")->count("id");
        }
    }

    public function countAgentMembers($timeMap, $reg_source_id)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User
                ->where("reg_source_id", "=", $reg_source_id)
                ->whereBetween("reg_time", $timeMap)
                ->whereNotNull("two_recommend_id")
                ->count("id");
        }else{
            return $this->Cx_User
                ->whereBetween("reg_time", $timeMap)
                ->whereNotNull("two_recommend_id")
                ->count("id");
        }
    }

    public function countEnvelopeMembers($timeMap, $reg_source_id)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User
                ->where("reg_source_id", "=", $reg_source_id)
                ->whereBetween("reg_time", $timeMap)
                ->whereNotNull("two_recommend_id")
                ->where("is_first_recharge", 1)
                ->count("id");
        }else{
            return $this->Cx_User
                ->whereBetween("reg_time", $timeMap)
                ->whereNotNull("two_recommend_id")
                ->where("is_first_recharge", 1)
                ->count("id");
        }

    }

    public function countActivePeopleNumber($timeMap, $reg_source_id)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User
                ->where("reg_source_id", "=", $reg_source_id)
                ->whereBetween("last_time", $timeMap)
                ->count("id");
        }else{
            return $this->Cx_User
                ->whereBetween("last_time", $timeMap)
                ->count("id");
        }
    }

    public function countFirstChargeNumber($timeMap, $reg_source_id)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')
                ->leftJoin('users as u','ubl.user_id','=','u.id')
                ->where('u.reg_source_id','=',$reg_source_id)
                ->whereBetween("ubl.time", $timeMap)
                ->where("ubl.is_first_recharge", 1)
                ->count("ubl.id");
        }else{
            return $this->Cx_User_Balance_Logs
                ->whereBetween("time", $timeMap)
                ->where("is_first_recharge", 1)
                ->count("id");
        }
    }

    public function countOrdinaryFirstChargeNumber($timeMap, $reg_source_id)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')
                ->leftJoin('users as u','ubl.user_id','=','u.id')
                ->where('u.reg_source_id','=',$reg_source_id)
                ->where(function($query){
                    $query->whereNull("u.two_recommend_id")
                        ->orWhere("u.two_recommend_id", 0);
                })
                ->whereBetween("ubl.time", $timeMap)
                ->where("ubl.type", 2)
                ->where("ubl.is_first_recharge", 1)
                ->count("ubl.id");

        }else{
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')
                ->leftJoin('users as u','ubl.user_id','=','u.id')
                ->where(function($query){
                    $query->whereNull("u.two_recommend_id")
                        ->orWhere("u.two_recommend_id", 0);
                })
                ->whereBetween("ubl.time", $timeMap)
                ->where("ubl.type", 2)
                ->where("ubl.is_first_recharge", 1)
                ->count("ubl.id");
        }
    }

    public function screenIds($ids, $status)
    {
        if ($status == 0) {
            return array_column($this->Cx_User->whereIn("id", $ids)->whereNull("two_recommend_id")->get("id")->toArray(), "id");
        } else {
            return array_column($this->Cx_User->whereIn("id", $ids)->whereNotNull("two_recommend_id")->get("id")->toArray(), "id");
        }
    }

    public function countAgentFirstChargeNumber($timeMap, $reg_source_id)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')
                ->leftJoin('users as u','ubl.user_id','=','u.id')
                ->where('u.reg_source_id','=',$reg_source_id)
                ->where(function($query){
                    $query->whereNotNull("u.two_recommend_id")
                        ->orWhere("u.two_recommend_id", '>', 0);
                })
                ->whereBetween("ubl.time", $timeMap)
                ->where("ubl.type", 2)
                ->where("ubl.is_first_recharge", 1)
                ->count("ubl.id");

        }else{
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')
                ->leftJoin('users as u','ubl.user_id','=','u.id')
                ->where(function($query){
                    $query->whereNotNull("u.two_recommend_id")
                        ->orWhere("u.two_recommend_id", '>', 0);
                })
                ->whereBetween("ubl.time", $timeMap)
                ->where("ubl.type", 2)
                ->where("ubl.is_first_recharge", 1)
                ->count("ubl.id");
        }
    }

    public function countRechargeUserNumber($timeMap, $reg_source_id)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User_Recharge_Logs->from('user_recharge_logs as url')
                ->leftJoin('users as u','url.user_id','=','u.id')
                ->where('u.reg_source_id','=',$reg_source_id)
                ->whereBetween("url.time", $timeMap)
                ->where("url.status", 2)
                ->count("url.id");

        }else{
            return $this->Cx_User_Recharge_Logs
                ->whereBetween("time", $timeMap)
                ->where("status", 2)
                ->count("id");
        }
    }

    public function sumRechargeMoney($reg_source_id, $timeMap)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User_Recharge_Logs->from('user_recharge_logs as url')->leftJoin('users as u','u.id','=','url.user_id')->where('u.reg_source_id','=',$reg_source_id)->where("url.status", 2)->whereBetween("url.time", $timeMap)->sum("arrive_money");
        }else{
            return $this->Cx_User_Recharge_Logs->whereBetween("time", $timeMap)->where("status", 2)->sum("arrive_money");
        }
    }

    public function sumBankCardRechargeMoney($reg_source_id, $timeMap)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')->leftJoin('users as u','u.id','=','ubl.user_id')->where('u.reg_source_id','=',$reg_source_id)->where("ubl.type", 16)->whereBetween("ubl.time", $timeMap)->sum("ubl.money");
        }else{
            return $this->Cx_User_Balance_Logs->whereBetween("time", $timeMap)->where("type", 16)->sum("money");
        }
    }

    public function sumWithdrawalMoney($reg_source_id, $timeMap)
    {
        if($reg_source_id >= 0){
            return $this->Cx_Withdrawal_Record->from('withdrawal_record as wr')->leftJoin('users as u','u.id','=','wr.user_id')
                ->where('u.reg_source_id','=',$reg_source_id)->whereBetween("wr.approval_time", $timeMap)->where("wr.status", 1)->sum("wr.payment");
        }else{
            return $this->Cx_Withdrawal_Record->whereBetween("approval_time", $timeMap)->where("status", 1)->sum("payment");
        }
    }

    public function sumUserBalance($reg_source_id)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User->where('reg_source_id', '=', $reg_source_id)->sum("balance");
        }else {
            return $this->Cx_User->sum("balance");
        }
    }

    public function sumUserCommission($reg_source_id)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User->where('reg_source_id', '=', $reg_source_id)->sum("commission");
        }else{
            return $this->Cx_User->sum("commission");
        }
    }

    public function sumSubCommission($reg_source_id, $timeMap)
    {
        if($reg_source_id >= 0){
            return $this->Cx_Charge_Logs->from('charge_logs as cl')->leftJoin('users as u','u.id','=','cl.charge_user_id')
                ->where("u.reg_source_id", '=', $reg_source_id)->whereBetween("cl.create_time", $timeMap)->sum("cl.money");
        }else{
            return $this->Cx_Charge_Logs->whereBetween("create_time", $timeMap)->sum("money");
        }
    }

    public function sumSubCommission2($reg_source_id, $timeMap)
    {
        if($reg_source_id >= 0){
            return $this->Cx_Charge_Logs->from('charge_logs as cl')->leftJoin('users as u', 'u.id', '=', 'cl.charge_user_id')->where('u.reg_source_id','=',$reg_source_id)->whereBetween("cl.create_time", $timeMap)->sum("cl.money");
        }else{
            return $this->Cx_Charge_Logs->from('charge_logs as cl')->leftJoin('users as u', 'u.id', '=', 'cl.charge_user_id')->whereBetween("cl.create_time", $timeMap)->sum("cl.money");
        }
    }

    public function getIds()
    {
        return array_column($this->Cx_User->get("id")->toArray(), "id");
    }

    public function getRegSourceIds($reg_source_id)
    {
        return array_column($this->Cx_User->where("reg_source_id", $reg_source_id)->get("id")->toArray(), "id");
    }

    public function getBettingOrder($reg_source_id, $timeMap)
    {
        if($reg_source_id >= 0){
            return $this->Cx_Game_Betting->from('game_betting as gb')->leftJoin('users as u', 'u.id', '=', 'gb.user_id')->where('u.reg_source_id','=',$reg_source_id)->whereBetween("gb.betting_time", $timeMap)->select("gb.id", "gb.money", "gb.service_charge", "gb.win_money", "gb.user_id")->get();
        }else{
            return $this->Cx_Game_Betting->from('game_betting as gb')->leftJoin('users as u', 'u.id', '=', 'gb.user_id')->whereBetween("gb.betting_time", $timeMap)->select("gb.id", "gb.money", "gb.service_charge", "gb.win_money", "gb.user_id")->get();
        }
    }

    public function countBettingNumber($ids, $timeMap)
    {
        return $this->Cx_Game_Betting->whereIn("user_id", $ids)->whereBetween("betting_time", $timeMap)->count("id");
    }

    public function sumBettingMoney($ids, $timeMap)
    {
        return $this->Cx_Game_Betting->whereIn("user_id", $ids)->whereBetween("betting_time", $timeMap)->sum("money");
    }

    public function sumServiceMoney($ids, $timeMap)
    {
        return $this->Cx_Game_Betting->whereIn("user_id", $ids)->whereBetween("betting_time", $timeMap)->sum("service_charge");
    }

    public function countPayEnvelope($ids, $timeMap)
    {
        return $this->Cx_Sign_Orders->whereIn("user_id", $ids)->whereBetween("start_time", $timeMap)->count("id");
    }

    public function sumPayEnvelope($ids, $timeMap)
    {
        return $this->Cx_Sign_Orders->whereIn("user_id", $ids)->whereBetween("start_time", $timeMap)->sum("amount");
    }

    public function sumUserProfit($ids, $timeMap)
    {
        return $this->Cx_Game_Betting->whereIn("user_id", $ids)->where("status", 1)->whereBetween("betting_time", $timeMap)->sum("win_money");
    }

    public function sumReceiveEnvelope($ids, $timeMap)
    {
        return $this->Cx_Sign_Orders->whereIn("user_id", $ids)->whereBetween("start_time", $timeMap)->sum("yet_receive_count");
    }

    public function sumBackstageGiftMoney($ids, $timeMap)
    {
        return $this->Cx_User_Balance_Logs->where("type", 8)->whereIn("user_id", $ids)->whereBetween("time", $timeMap)->sum("money");
    }

    public function sumBackstageGiftMoney2($reg_source_id, $timeMap)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')->leftJoin('users as u','u.id','=','ubl.user_id')->where("ubl.type", 8)->where('u.reg_source_id','=',$reg_source_id)->whereBetween("ubl.time", $timeMap)->sum("ubl.money");
        }else{
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')->leftJoin('users as u','u.id','=','ubl.user_id')->where("ubl.type", 8)->whereBetween("ubl.time", $timeMap)->sum("ubl.money");
        }
    }

    public function sumDownSeparation($ids, $timeMap)
    {
        return $this->Cx_User_Balance_Logs->where("type", 10)->whereIn("user_id", $ids)->whereBetween("time", $timeMap)->sum("money");
    }

    public function sumDownSeparation2($reg_source_id, $timeMap)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')->leftJoin('users as u','u.id','=','ubl.user_id')->where("ubl.type", 10)->where('u.reg_source_id','=',$reg_source_id)->whereBetween("ubl.time", $timeMap)->sum("money");
        }else{
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')->leftJoin('users as u','u.id','=','ubl.user_id')->where("ubl.type", 10)->whereBetween("ubl.time", $timeMap)->sum("money");
        }

    }

    public function sumUpperSeparation($ids, $timeMap)
    {
        return $this->Cx_User_Balance_Logs->where("type", 9)->whereIn("user_id", $ids)->whereBetween("time", $timeMap)->sum("money");
    }

    public function sumUpperSeparation2($reg_source_id, $timeMap)
    {
        if($reg_source_id >= 0){
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')->leftJoin('users as u','u.id','=','ubl.user_id')->where("ubl.type", 9)->where('u.reg_source_id','=',$reg_source_id)->whereBetween("ubl.time", $timeMap)->sum("ubl.money");
        }else{
            return $this->Cx_User_Balance_Logs->from('user_balance_logs as ubl')->leftJoin('users as u','u.id','=','ubl.user_id')->where("ubl.type", 9)->whereBetween("ubl.time", $timeMap)->sum("ubl.money");
        }
    }

    public function getSignOrders($reg_source_id, $timeMap)
    {
        if($reg_source_id >= 0){
            return $this->Cx_Sign_Orders->from('sign_orders as so')->leftJoin('users as u','u.id','=','so.user_id')->where('u.reg_source_id','=',$reg_source_id)->whereBetween("so.start_time", $timeMap)->select("so.id", "so.yet_receive_count", "so.amount");
        }else{
            return $this->Cx_Sign_Orders->whereBetween("start_time", $timeMap)->select("id", "yet_receive_count", "amount");
        }
    }

    public function sumOnlineNum()
    {
//        $redisConfig = config('database.redis.default');
//        $redis = new Client($redisConfig);
        $redis = DRedis::getInstance();
        $num = $redis->scard('swoft:ONLINE_USER_ID');
        return $num;
    }
}
