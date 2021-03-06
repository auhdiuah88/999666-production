<?php


namespace App\Repositories\Api;


use App\Models\Cx_Charge_Logs;
use App\Models\Cx_Settings;
use App\Models\Cx_Withdrawal_Record;

class WithdrawalRepository
{
    private $Cx_Withdrawal_Record, $Cx_Charge_Logs, $Cx_Settings;

    public function __construct
    (
        Cx_Withdrawal_Record $cx_Withdrawal_Record,
        Cx_Charge_Logs $charge_Logs,
        Cx_Settings $cx_Settings
    )
    {
        $this->Cx_Withdrawal_Record = $cx_Withdrawal_Record;
        $this->Cx_Charge_Logs = $charge_Logs;
        $this->Cx_Settings = $cx_Settings;
    }

    public function findRecordByUserId($where, $size)
    {
        return makeModel($where, $this->Cx_Withdrawal_Record)
            ->with([
                "bank" => function ($query) {
                    $query->select("id", "bank_num");
                }
            ])
            ->orderByDesc('create_time')
            ->paginate($size);
    }

    public function addRecord($data)
    {
        return $this->Cx_Withdrawal_Record->insertGetId($data);
    }

    public function getMessage($id)
    {
        return $this->Cx_Withdrawal_Record->select(["id", "message"])->where("id", $id)->first();
    }

    public function getAgentWithdrawalRecord($userId)
    {
        return $this->Cx_Withdrawal_Record->with(["bank" => function ($query) {
            $query->select("id", "bank_num");
        }])->where("type", 1)->where("user_id", $userId)->orderByDesc("create_time")->get()->toArray();
    }

    public function countAgentWithdrawalRecord($userId)
    {
        return $this->Cx_Withdrawal_Record->where("user_id", $userId)->count("id");
    }

    public function countUserWithdraw($userId)
    {
        return $this->Cx_Withdrawal_Record->where("user_id", $userId)->whereIn("status", [0,1])->whereBetween("create_time",[day_start(),day_end()])->count("id");
    }

    public function sumUserWithdraw($userId)
    {
        return $this->Cx_Withdrawal_Record->where("user_id", $userId)->where("status", 0)->sum("money");
    }

    public function getAgentRewardRecord($user_id, $type)
    {
        return $this->Cx_Charge_Logs->with(["user" => function ($query) {
            $query->select("id", "nickname");
        }])->where("charge_user_id", $user_id)->where("type", $type)->orderByDesc("create_time")->get()->toArray();
    }

    /**
     *  ??????????????????
     */
//    public function addWithdrawalLog(object $user, $money, $order_no, $pltf_order_no, $upi_id, $account_holder, $bank_number, $bank_name, $ifsc_code, $email,$type=0)
//    {
//        $data = [
//            'user_id' => $user->id,
//            'phone' => $user->phone,
//            'nickname' => $user->nickname,
//            'money' => $money,
//            'create_time' => time(),
//            'order_no' => $order_no,
//            'pltf_order_no' => $pltf_order_no,
//            'upi_id' => $upi_id,
//            'account_holder' => $account_holder,
//            'bank_number' => $bank_number,
//            'bank_name' => $bank_name,
//            'ifsc_code' => $ifsc_code,
//            'pay_status' => 0,
//            'type' => 0,
//            'status' => 0,
//            'email' => $email,
//            'type' => $type,  // ?????????0:???????????? 1:??????????????????
//
//            'service_charge'=> 45,
//
//        ];
//        return $this->Cx_Withdrawal_Record->insert($data);
//    }

    /**
     * ??????????????????????????????
     */
    public function getWithdrawalInfoByCondition(array $where)
    {
        if(isset($where['plat_order_id']))unset($where['plat_order_id']);
        if(isset($where['pay_status']))unset($where['pay_status']);
        return $this->Cx_Withdrawal_Record->where($where)->first();
    }

    /**
     * ??????????????????
     * @return array
     */
    public function getConfig()
    {
        $setting = $this->Cx_Settings->where('setting_key', 'withdraw')->first();
        if(!$setting)return [];
        return $setting['setting_value'];
    }
}
