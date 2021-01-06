<?php


namespace App\Repositories\Admin;


use App\Models\Cx_User;
use App\Models\Cx_User_Balance_Logs;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository
{
    private $Cx_User, $Cx_User_Balance_Logs;

    public function __construct(Cx_User $cx_User, Cx_User_Balance_Logs $balance_Logs)
    {
        $this->Cx_User = $cx_User;
        $this->Cx_User_Balance_Logs = $balance_Logs;
    }

    public function findAll($offset, $limit)
    {
        return $this->Cx_User->where("is_customer_service", 0)->orderByDesc("last_time")->offset($offset)->limit($limit)->select(["*", "id as total_win_money"])->get()->setAppends(['online_status'])->toArray();
    }

    public function getBalanceLogs($userId, $offset, $limit)
    {
        return $this->Cx_User_Balance_Logs->with(["admin" => function ($query) {
            $query->select(["id", "nickname"]);
        }])->where("user_id", $userId)->offset($offset)->limit($limit)->orderByDesc("time")->select(["*", "type as type_map"])->get()->toArray();
    }

    public function countBalanceLogs($userId)
    {
        return $this->Cx_User_Balance_Logs->where("user_id", $userId)->count("id");
    }

    public function getRecommenders()
    {
        return $this->Cx_User->orderByDesc("last_time")->select(["id", "nickname"])->get()->toArray();
    }

    public function countAll()
    {
        return $this->Cx_User->where("is_customer_service", 0)->count("id");
    }

    public function findById($id)
    {
        return $this->Cx_User->where("id", $id)->first();
    }

    public function findByPhone($phone)
    {
        return $this->Cx_User->where("phone", $phone)->select(['id', 'nickname', 'phone'])->first();
    }

    public function findCustomerServiceByPhone($phone)
    {
        return $this->Cx_User->where("phone", $phone)->where("is_customer_service", 1)->select(['id', 'nickname', 'phone'])->first();
    }

    public function getUserByConditions($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->Cx_User)->offset($offset)->limit($limit)->orderByDesc("last_time")->select(['*', 'id as total_win_money'])->get()->toArray();
    }

    public function countUserByConditions($data)
    {
        return $this->whereCondition($data, $this->Cx_User)->count("id");
    }

    public function addUser($data)
    {
        return $this->Cx_User->insertGetId($data);
    }

    public function editUser($data)
    {
        return $this->Cx_User->where("id", $data["id"])->update($data);
    }

    public function delUser($id)
    {
        return $this->Cx_User->where("id", $id)->delete();
    }

    public function batchModifyRemarks(array $ids, string $message)
    {
        return $this->Cx_User->whereIn("id", $ids)->update(["remarks" => $message]);
    }

    public function modifyUserStatus($id, $status)
    {
        return $this->Cx_User->where("id", $id)->update(["status" => $status]);
    }

    public function getCustomerService()
    {
        return $this->Cx_User->where("is_customer_service", 1)->select(["id", "phone"])->get()->toArray();
    }

    public function modifyCustomerService($ids, $customer_id)
    {
        return $this->Cx_User->whereIn("id", $ids)->update(["customer_service_id" => $customer_id]);
    }

    public function modifyEmptyAgent($ids, $data)
    {
        return $this->Cx_User->whereIn("id", $ids)->update($data);
    }

    public function addLogs($data)
    {
        return $this->Cx_User_Balance_Logs->insertGetId($data);
    }

    /**
     * $user_id 用户id
     * $money 变动金额
     * $type 场景 1.下注 2.充值 3.提现 4.签到礼金 5.红包礼金 6.投注获胜 7.签到零回扣 8.后台赠送礼金 9.手动上分 10.手动下分 11.提现驳回
     * $msg 描述
     * $dq_balance 当前金额
     * $wc_balance 操作后金额
     * 只记录用户余额变动记录
     */
    public function addBalanceLog($user_id, $money, $type, $msg, $dq_balance, $wc_balance)
    {
        $admin_id = request()->get('admin_id');
        // 余额变动记录
        $data = [
            "user_id" => $user_id,
            "type" => $type,
            "dq_balance" => $dq_balance,
            "wc_balance" => $wc_balance,
            "time" => time(),
            "msg" => $msg,
            "money" => abs($money),
//            "is_first_recharge" => $user->is_first_recharge == 1 ? 1 : 0,
            'admin_id' => $admin_id
        ];
        return $this->Cx_User_Balance_Logs->insert($data);
    }

    /**
     * 修改用户假流水
     * @param $user_id
     * @param $money
     * @return mixed
     */
    public function editFakeBettingMoney($user_id, $money)
    {
        return $this->Cx_User->where('id', $user_id)->update(['fake_betting_money' => $money]);
    }
    public function findGroupLeaders($where, $offset, $limit)
    {
        return $this->Cx_User->groupLeader()->where($where)->offset($offset)->limit($limit)->orderByDesc("last_time")->get()->toArray();
    }

    public function countGroupLeaders($where)
    {
        return $this->Cx_User->groupLeader()->where($where)->count();
    }

    public function logicDel($id)
    {
        return $this->Cx_User->where('id',$id)->update(['deleted_at' => time()]);
    }

    public function searchAccount($phone)
    {
        return $this->Cx_User->where([
            ['phone', $phone],
            ['is_group_leader',  2],
            ['is_customer_service', 1]
        ])->select(['id', 'nickname', 'phone'])->first();
    }
}
