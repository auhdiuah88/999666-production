<?php


namespace App\Services\Admin;


use App\Dictionary\BalanceTypeDic;
use App\Models\Cx_User;
use App\Repositories\Admin\UserRepository as Repository;
use App\Repositories\Api\UserRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class UserService extends BaseService
{
    protected $UserRepository, $ApiUserRepository;

    public function __construct(Repository $userRepository, UserRepository $repository)
    {
        $this->UserRepository = $userRepository;
        $this->ApiUserRepository = $repository;
    }

    public function findAll($page, $limit)
    {
        $list = $this->UserRepository->findAll(($page - 1) * $limit, $limit);
        $total = $this->UserRepository->countAll();
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function findById($id)
    {
        $this->_data = $this->UserRepository->findById($id);
    }

    public function findCustomerServiceByPhone($phone)
    {
         $data = $this->UserRepository->findCustomerServiceByPhone($phone);
         if($data){
            $this->_data = $data;
         }else{
             $this->_code = 402;
             $this->_msg = '用户不存在';
         }
    }

    public function addUser($data)
    {
        if ($this->ApiUserRepository->findByPhone($data["phone"])) {
            $this->_code = 402;
            $this->_msg = "账号已存在";
            return false;
        }
        $data["reg_time"] = time();
        $data["code"] = $this->ApiUserRepository->getcode();
        $data["reg_source_id"] = 1;
        $data["password"] = Crypt::encrypt($data["password"]);
        if (!array_key_exists("nickname", $data)) {
            $data["nickname"] = "用户" . md5($data["phone"]);
        } elseif (!$data["nickname"]) {
            $data["nickname"] = "用户" . md5($data["phone"]);
        }
//        $data = $this->assembleData($data);
        $data = $this->initRelation($data);
        if(!$data){
            $this->_code = 402;
            $this->_msg = "推荐人不存在";
            return false;
        }
        if ($this->UserRepository->addUser($data)) {
            $this->_msg = "添加成功";
            return true;
        } else {
            $this->_code = 402;
            $this->_msg = "添加失败";
            return false;
        }
    }

    public function initRelation($data)
    {
        if (array_key_exists("two_recommend_id", $data) && $data["two_recommend_id"]) {
            $two = $this->UserRepository->findById($data["two_recommend_id"]);
            if(!$two)
                return false;
            $data['one_recommend_phone'] = $two->two_recommend_phone;
            $data['one_recommend_id'] = $two->two_recommend_id;
            $data["two_recommend_phone"] = $two->phone;
            $data["invite_relation"] = '-' . trim($data["two_recommend_id"] . '-' . trim($two->invite_relation,'-'),'-') . '-';
        }
        return $data;
    }

    public function editUser($data)
    {
        if (array_key_exists("password", $data)) {
            $data["password"] = Crypt::encrypt($data["password"]);
        }
        if (array_key_exists("balance", $data)) {
            $this->_msg = "余额不能修改";
            $this->_code = 402;
            return;
        }
//        $data = $this->assembleData($data);
        $user = $this->UserRepository->findById($data['id']);
        if(isset($data['two_recommend_id']) && $data['two_recommend_id'] && $user->two_recommend_id != $data['two_recommend_id']){
            ##判断新的上级是否是自己的下级
            if(strpos($user->invite_relation,"-{$data['two_recommend_id']}-")){
                $this->_msg = "新的推荐人不能为该用户的下级";
                $this->_code = 402;
                return;
            }
            $initRelation = true;
            $old_two_recommend_id = $user->two_recommend_id;
            $data = $this->initRelation($data);
            if(!$data){
                $this->_code = 402;
                $this->_msg = "推荐人不存在";
                return;
            }
        }
        DB::beginTransaction();
        try{
            if(isset($initRelation) && $initRelation){
                ##更新所有的二级推荐人
                $res = $this->updateGroupRelation($data, $old_two_recommend_id);
                if(!$res)throw new \Exception('操作失败');
            }
            $res = $this->UserRepository->editUser($data);
            if($res === false)throw new \Exception('用户修改失败');
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            $this->_msg = $e->getMessage();
            $this->_code = 402;
        }
    }

    public function updateGroupRelation($data, $old_two_recommend_id)
    {
        ##更新老二级推荐人的二级人数
        if($old_two_recommend_id){
            $res = $this->UserRepository->subTwoNumber($old_two_recommend_id, 1);
            if($res === false)return false;
        }
        $res = $this->UserRepository->addTwoNumber($data['two_recommend_id'], 1);
        if($res === false)return false;
        $num = $this->UserRepository->countOneRecomBumByTwo($data);
        if($num > 0){
            ##二级推荐人 - 一级推荐人数
            if($old_two_recommend_id){
                $res = $this->UserRepository->subOneNumber($old_two_recommend_id, $num);
                if($res === false)return false;
            }
            $res = $this->UserRepository->addOneNumber($data['two_recommend_id'], $num);
            if($res === false)return false;
            ##更新二级推荐人是改用的一级推荐人信息
            $res = $this->UserRepository->updateOneRecomByTwo($data);
            if($res === false)return false;
        }
        ##更新邀请关系
        $res = $this->UserRepository->updateInviteRelation($data);
        if($res === false)return false;
        return true;
    }


    public function delUser($id)
    {
        if ($this->UserRepository->delUser($id)) {
            ##删除缓存
            Cache::forget(Cx_User::CACHE_USER_PROFILE . $id);
            $this->_msg = "删除成功";
        } else {
            $this->_code = 402;
            $this->_msg = "删除失败";
        }
    }

    public function searchUser($data)
    {
        $page = $data["page"];
        $limit = $data["limit"];
        $list = $this->UserRepository->getUserByConditions($data, ($page - 1) * $limit, $limit);
        $total = $this->UserRepository->countUserByConditions($data);
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function batchModifyRemarks(array $ids, string $message)
    {
        if ($this->UserRepository->batchModifyRemarks($ids, $message)) {
            $this->_msg = "修改备注成功";
        } else {
            $this->_code = 402;
            $this->_msg = "修改备注失败";
        }
    }

    public function getRecommenders()
    {
        $this->_data = $this->UserRepository->getRecommenders();
    }

    public function modifyUserStatus($id, $status)
    {
        if ($this->UserRepository->modifyUserStatus($id, $status)) {
            $this->_msg = "修改成功";
        } else {
            $this->_code = 402;
            $this->_msg = "修改失败";
        }
    }

    public function assembleData($data)
    {
        if (array_key_exists("one_recommend_id", $data) && $data["one_recommend_id"]) {
            $one = $this->UserRepository->findById($data["one_recommend_id"]);
            $data["one_recommend_phone"] = $one->phone;
        }
        if (array_key_exists("two_recommend_id", $data) && $data["two_recommend_id"]) {
            $two = $this->UserRepository->findById($data["two_recommend_id"]);
            $data["two_recommend_phone"] = $two->phone;
        }
        return $data;
    }

    public function getCustomerService()
    {
        $this->_data = $this->UserRepository->getCustomerService();
    }

    public function modifyCustomerService($ids, $customer_id)
    {
        $data = [
            "one_recommend_id" => null,
            "two_recommend_id" => null,
            "one_recommend_phone" => null,
            "two_recommend_phone" => null
        ];
        DB::beginTransaction();
        try {
            $this->UserRepository->modifyEmptyAgent($ids, $data);
            if ($this->UserRepository->modifyCustomerService($ids, $customer_id)) {
                $this->_msg = "修改客服成功";
            } else {
                $this->_code = 402;
                $this->_msg = "修改客服失败";
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->_code = $e->getCode();
            $this->_msg = $e->getMessage();
        }
    }

    public function giftMoney($id, $money, $token)
    {
        $adminId = $this->getUserId($token);
        DB::beginTransaction();
        try {
            $user = $this->UserRepository->findById($id);
            $data = [
                "user_id" => $id,
                "type" => 8,
                "dq_balance" => $user->balance,
                "wc_balance" => bcadd($user->balance, $money, 2),
                "time" => time(),
                "msg" => "后台赠送" . $user->nickname . " " . $money . "元礼金!",
                "money" => $money,
                "admin_id" => $adminId
            ];
            $this->UserRepository->addLogs($data);
            $update = ["id" => $id, "balance" => bcadd($user->balance, $money, 2)];
            if ($this->UserRepository->editUser($update)) {
                $this->_msg = "赠送成功";
            } else {
                $this->_code = 200;
                $this->_msg = "赠送失败";
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->_code = $e->getCode();
            $this->_msg = $e->getMessage();
        }
    }

    public function upperSeparation($id, $money, $token)
    {
        $adminId = $this->getUserId($token);
        DB::beginTransaction();
        try {
            $user = $this->UserRepository->findById($id);
            $data = [
                "user_id" => $id,
                "type" => 9,
                "dq_balance" => $user->balance,
                "wc_balance" => bcadd($user->balance, $money, 2),
                "time" => time(),
                "msg" => "后台手动为" . $user->nickname . "上分" . $money . "元!",
                "money" => $money,
                "admin_id" => $adminId
            ];
            $this->UserRepository->addLogs($data);
            $update = ["id" => $id, "balance" => bcadd($user->balance, $money, 2)];
            if ($this->UserRepository->editUser($update)) {
                $this->_msg = "上分成功";
            } else {
                $this->_code = 200;
                $this->_msg = "上分失败";
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->_msg = $e->getMessage();
            $this->_code = $e->getCode();
        }
    }


    public function downSeparation($id, $money, $token)
    {
        $adminId = $this->getUserId($token);
        DB::beginTransaction();
        try {
            $user = $this->UserRepository->findById($id);
            $data = [
                "user_id" => $id,
                "type" => 10,
                "dq_balance" => $user->balance,
                "wc_balance" => bcsub($user->balance, $money, 2),
                "time" => time(),
                "msg" => "后台手动为" . $user->nickname . "下分" . $money . "元!",
                "money" => $money,
                "admin_id" => $adminId
            ];
            $this->UserRepository->addLogs($data);
            $update = ["id" => $id, "balance" => bcsub($user->balance, $money, 2)];
            if ($this->UserRepository->editUser($update)) {
                $this->_msg = "下分成功";
            } else {
                $this->_code = 200;
                $this->_msg = "下分失败";
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->_msg = $e->getMessage();
            $this->_code = $e->getCode();
        }
    }

    public function getBalanceLogs($id, $page, $limit, $type)
    {
        $list = $this->UserRepository->getBalanceLogs($id, ($page - 1) * $limit, $limit, $type);
        $total = $this->UserRepository->countBalanceLogs($id, $type);
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function getBalanceType()
    {
        $this->_data = array_values(BalanceTypeDic::lists());
    }

    public function editFakeBettingMoney(){
        $user_id = $this->intInput('user_id');
        $money = $this->floatInput('money');
        $res = $this->UserRepository->editFakeBettingMoney($user_id, $money);
        if($res === false){
            $this->_msg = "操作失败";
            $this->_code = 402;
            return false;
        }
        $this->_msg = "操作成功";
        return true;
    }

    public function clearFakeBetting()
    {
        $this->UserRepository->clearFakeBetting();
    }

    public function searchUserByPhoneLike()
    {
        $phoneLike = $this->strInput('phone');
        if(!$phoneLike || strlen($phoneLike) < 5){
            $this->_code = 402;
            $this->_msg = '请至少输入五位手机号';
            return false;
        }
        $this->_data = $this->UserRepository->searchUser(['phone'=>['like', "%{$phoneLike}%"]]);
        return true;
    }

    public function groupUpList(): bool
    {
        $user_id = $this->intInput('user_id');
        $size = $this->sizeInput();
        $user = $this->UserRepository->findById($user_id);
        if(!$user){
            $this->_code = 402;
            $this->_msg = '用户不存在';
            return false;
        }
        $relation = trim($user['invite_relation'],'-');
        $relation = explode('-',$relation);
        $where = [
            'id' => ['in', $relation]
        ];
        $this->_data = $this->UserRepository->groupUpList($where, $size, $relation);
        return true;
    }

    public function groupDownList()
    {
        $user_id = $this->intInput('user_id');
        $size = $this->sizeInput();
        $where = [
            'invite_relation' => ['like', "%-{$user_id}-%"]
        ];
        $this->_data = $this->UserRepository->groupDownList($where, $size);
    }

}
