<?php


namespace App\Repositories\Admin\agent;


use App\Models\Cx_Admin;
use App\Models\Cx_User;
use App\Models\Cx_User_Recharge_Logs;
use Illuminate\Support\Facades\DB;

class AgentUserRepository
{

    private $Cx_User, $Cx_Admin, $Cx_User_Recharge_logs;

    public function __construct(Cx_User $cx_User, Cx_Admin $cx_Admin, Cx_User_Recharge_Logs $cx_User_Recharge_Logs){
        $this->Cx_User = $cx_User;
        $this->Cx_Admin = $cx_Admin;
        $this->Cx_User_Recharge_logs = $cx_User_Recharge_Logs;
    }

    public function getAdminUserId($admin_id){
        return $this->Cx_Admin->where("id", $admin_id)->select(['id', 'username', 'user_id', 'status'])->first();
    }

    public function searchUser($where, $size){
        $model = makeModel($where, $this->Cx_User);
        $list = $model
            ->orderBy('reg_time', 'desc')
            ->select(['*', 'phone as phone_hide', 'one_recommend_phone as one_recommend_phone_hide', 'two_recommend_phone as two_recommend_phone_hide'])
            ->paginate($size);
        return $list;
    }

    public function firstRechargeList($where, $size){
        $model = makeModel($where, $this->Cx_User_Recharge_logs);
//        DB::connection()->enableQueryLog();
        $list = $model->from('user_recharge_logs as r')
            ->leftJoin('users as u','r.user_id', '=', 'u.id')
            ->with(
                [
                    'user' => function($query){
                        $query->select(['id', 'nickname', 'phone', 'total_recharge', 'cl_withdrawal', 'commission', 'one_recommend_phone as one_recommend_phone_hide', 'two_recommend_phone as two_recommend_phone_hide', 'balance']);
                    }
                ]
            )
            ->select(['r.*'])
            ->orderBy('reg_time', 'desc')
            ->paginate($size);
//        print_r(DB::getQueryLog());die;
        return $list;
    }

    public function orderInfoList($where, $size){
        $model = makeModel($where, $this->Cx_User);
        return $model
            ->select(['id', 'nickname', 'phone as phone_hide', 'total_recharge', 'cl_withdrawal', 'balance', 'commission', 'status', 'cl_betting', 'cl_betting_total'])
            ->with(
                [
                    'recharge' => function($query){
                        $query->where([['status', '=', 2], ['is_first_recharge', '=', 1]])->orderBy('arrive_time', 'asc')->select(['user_id', 'arrive_time']);
                    }
                ]
            )
            ->paginate($size);
    }

    public function getUserIds($where){
        return $user_ids = makeModel($where, $this->Cx_User)->pluck('id')->toArray();
    }

    public function getLikePhoneUserId($phone){
        return $this->Cx_User->where('phone', 'like', "%{$phone}%")->pluck('id')->toArray();
    }

}
