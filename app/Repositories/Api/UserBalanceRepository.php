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
        $list =  makeModel($where, $this->Cx_User_Balance_Logs)
            ->select(["id", "money", "time"])
            ->orderByDesc('time')
            ->paginate($size);
        if(!$list->isEmpty()){
            foreach ($list as &$item){
                $item->order_no = date('YmdHis',$item->time) . rand(10000000, 99999999);
            }
        }
        return $list;
    }

}
