<?php


namespace App\Repositories\Api;


use App\Models\Cx_Charge_Logs;
use App\Models\Cx_Withdrawal_Record;

class WithdrawalRepository
{
    private $Cx_Withdrawal_Record, $Cx_Charge_Logs;

    public function __construct(Cx_Withdrawal_Record $cx_Withdrawal_Record, Cx_Charge_Logs $charge_Logs)
    {
        $this->Cx_Withdrawal_Record = $cx_Withdrawal_Record;
        $this->Cx_Charge_Logs = $charge_Logs;
    }

    public function findRecordByUserId($userId)
    {
        return $this->Cx_Withdrawal_Record->with(["bank" => function ($query) {
            $query->select("id", "bank_num");
        }])->where("user_id", $userId)->get()->toArray();
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
        }])->where("user_id", $userId)->orderByDesc("create_time")->get()->toArray();
    }

    public function countAgentWithdrawalRecord($userId)
    {
        return $this->Cx_Withdrawal_Record->where("user_id", $userId)->count("id");
    }

    public function getAgentRewardRecord($user_id)
    {
        return $this->Cx_Charge_Logs->with(["user" => function ($query) {
            $query->select("id", "nickname");
        }])->where("charge_user_id", $user_id)->orderByDesc("create_time")->get()->toArray();
    }

    /**
     *  添加提款记录
     */
    public function addWithdrawalLog(object $user, $money, $order_no, $pltf_order_no, $upi_id,$account_holder,$bank_number,$bank_name,$ifsc_code,$sign)
    {
        $data = [
            'user_id' => $user->id,
            'phone' => $user->phone,
            'nickname' => $user->nickname,
            'money' => $money,
            'create_time' => time(),
            'order_no' => $order_no,
            'pltf_order_no' => $pltf_order_no,
            'upi_id' => $upi_id,
            'account_holder' => $account_holder,
            'bank_number' => $bank_number,
            'bank_name' => $bank_name,
            'ifsc_code' => $ifsc_code,
            'sign' => $sign,
            'pay_status' => 0,
            'type' => 0,
            'status' => 0,
        ];
        $this->Cx_Withdrawal_Record->insert($data);
    }


}
