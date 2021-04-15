<?php


namespace App\Repositories\Api;


use App\Models\Cx_User_Balance_Logs;
use Illuminate\Support\Facades\DB;

class UserBalanceRepository
{

    protected $Cx_User_Balance_Logs;

    public function __construct
    (
        Cx_User_Balance_Logs $cx_User_Balance_Logs
    )
    {
        $this->Cx_User_Balance_Logs = $cx_User_Balance_Logs;
    }

    public function getAddBalanceLogList($size)
    {
        $where['user_id'] = ['=', request()->get('userInfo')['id']];
        $where['type'] = ['=', 9];
        return $this->getAddReduceLog($where, $size);
    }

    public function getReduceBalanceLogList($size)
    {
        $where['user_id'] = ['=', request()->get('userInfo')['id']];
        $where['type'] = ['=', 10];
        return $this->getAddReduceLog($where, $size);
    }

    public function getAddReduceLog($where, $size)
    {
        $bankNameList = [
            "Vietcombank",
            "VPBank",
            "ABBANK",
            "VietinBank"
        ];
        $cardsList = [
            "76210000858656",
            "050116387309"
        ];
        $list =  makeModel($where, $this->Cx_User_Balance_Logs)
            ->select(["id", "money", "time"])
            ->orderByDesc('time')
            ->paginate($size);
        $card = $cardsList[rand(0,count($cardsList)-1)];
        if(!$list->isEmpty()){
            foreach ($list as &$item){
                $item->order_no = date('YmdHis',$item->time) . rand(1000000000, 9999999999);
                $item->bank_number = $card;
                $item->bank_name = $bankNameList[rand(0,count($bankNameList)-1)];
            }
        }

        return $list;
    }

}
