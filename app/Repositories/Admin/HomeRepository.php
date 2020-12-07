<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Charge_Logs;
use App\Models\Cx_Game_Betting;
use App\Models\Cx_Sign_Order;
use App\Models\Cx_User;
use App\Models\Cx_User_Balance_Logs;
use App\Models\Cx_Withdrawal_Record;
use App\Repositories\BaseRepository;

class HomeRepository extends BaseRepository
{
    private
        $Cx_User,
        $Cx_User_Balance_Logs,
        $Cx_Withdrawal_Record,
        $Cx_Charge_Logs,
        $Cx_Game_Betting,
        $Cx_Sign_Orders;

    public function __construct(
        Cx_User $cx_User,
        Cx_User_Balance_Logs $balance_Logs,
        Cx_Withdrawal_Record $cx_Withdrawal_Record,
        Cx_Charge_Logs $charge_Logs,
        Cx_Game_Betting $game_Betting,
        Cx_Sign_Order $cx_Sign_Order
    )
    {
        $this->Cx_User = $cx_User;
        $this->Cx_User_Balance_Logs = $balance_Logs;
        $this->Cx_Withdrawal_Record = $cx_Withdrawal_Record;
        $this->Cx_Charge_Logs = $charge_Logs;
        $this->Cx_Game_Betting = $game_Betting;
        $this->Cx_Sign_Orders = $cx_Sign_Order;
    }

    public function findAll($offset, $limit)
    {
        return $this->Cx_User->select(["id", "nickname", "phone", "balance"])->offset($offset)->limit($limit)->get();
    }

    public function countAll()
    {
        return $this->Cx_User->where("is_customer_service", 1)->count("id");
    }

    public function sumGiveMoney($ids, $timeMap)
    {
        return $this->Cx_User_Balance_Logs->where("type", 5)->whereIn("user_id", $ids)->whereBetween("time", $timeMap)->sum("money");
    }

    public function countNewMembers($timeMap, $ids)
    {
        return $this->Cx_User->whereIn("id", $ids)->whereBetween("reg_time", $timeMap)->count("id");
    }

    public function countMembers($ids)
    {
        return $this->Cx_User->whereIn("id", $ids)->count("id");
    }

    public function countOrdinaryMembers($timeMap, $ids)
    {
        return $this->Cx_User->whereIn("id", $ids)->whereBetween("reg_time", $timeMap)->whereNull("two_recommend_id")->count("id");
    }

    public function countAgentMembers($timeMap, $ids)
    {
        return $this->Cx_User
            ->whereIn("id", $ids)
            ->whereBetween("reg_time", $timeMap)
            ->whereNotNull("two_recommend_id")
            ->count("id");
    }

    public function countEnvelopeMembers($timeMap, $ids)
    {
        return $this->Cx_User
            ->whereIn("id", $ids)
            ->whereBetween("reg_time", $timeMap)
            ->whereNotNull("two_recommend_id")
            ->where("is_first_recharge", 1)
            ->count("id");
    }

    public function countActivePeopleNumber($timeMap, $ids)
    {
        return $this->Cx_User
            ->whereIn("id", $ids)
            ->whereBetween("last_time", $timeMap)
            ->count("id");
    }

    public function countFirstChargeNumber($timeMap, $ids)
    {
        return $this->Cx_User
            ->whereIn("id", $ids)
            ->whereBetween("reg_time", $timeMap)
            ->where("is_first_recharge", 1)
            ->count("id");
    }

    public function countOrdinaryFirstChargeNumber($timeMap, $ids)
    {
        return $this->Cx_User
            ->whereIn("id", $ids)
            ->whereBetween("reg_time", $timeMap)
            ->whereNull("two_recommend_id")
            ->where("is_first_recharge", 1)
            ->count("id");
    }

    public function countAgentFirstChargeNumber($timeMap, $ids)
    {
        return $this->Cx_User
            ->whereIn("id", $ids)
            ->whereBetween("reg_time", $timeMap)
            ->whereNotNull("two_recommend_id")
            ->where("is_first_recharge", 1)
            ->count("id");
    }

    public function sumRechargeMoney($ids, $timeMap)
    {
        return $this->Cx_User_Balance_Logs->whereIn("user_id", $ids)->where("type", 2)->whereBetween("time", $timeMap)->sum("money");
    }

    public function sumWithdrawalMoney($ids, $timeMap)
    {
        return $this->Cx_Withdrawal_Record->whereIn("user_id", $ids)->whereBetween("approval_time", $timeMap)->where("status", 1)->sum("payment");
    }

    public function sumUserBalance($ids)
    {
        return $this->Cx_User->whereIn("id", $ids)->sum("balance");
    }

    public function sumUserCommission($ids)
    {
        return $this->Cx_User->whereIn("id", $ids)->sum("commission");
    }

    public function sumSubCommission($ids, $timeMap)
    {
        return $this->Cx_Charge_Logs->whereIn("charge_user_id", $ids)->whereBetween("create_time", $timeMap)->sum("money");
    }

    public function getIds()
    {
        return array_column($this->Cx_User->get("id")->toArray(), "id");
    }

    public function getRegSourceIds($reg_source_id)
    {
        return array_column($this->Cx_User->where("reg_source_id", $reg_source_id)->get("id")->toArray(), "id");
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
        return $this->Cx_Game_Betting->whereIn("user_id", $ids)->whereBetween("betting_time", $timeMap)->sum("win_money");
    }

    public function sumReceiveEnvelope($ids, $timeMap)
    {
        return $this->Cx_Sign_Orders->whereIn("user_id", $ids)->whereBetween("start_time", $timeMap)->sum("yet_receive_count");
    }

    public function sumBackstageGiftMoney($ids, $timeMap)
    {
        return $this->Cx_User_Balance_Logs->whereIn("user_id", $ids)->whereBetween("time", $timeMap)->sum("money");
    }
}
