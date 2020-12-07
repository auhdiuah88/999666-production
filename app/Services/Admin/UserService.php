<?php


namespace App\Services\Admin;


use App\Repositories\Admin\UserRepository as Repository;
use App\Repositories\Api\UserRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class UserService extends BaseService
{
    private $UserRepository, $ApiUserRepository;

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
        $data = $this->assembleData($data);
        if ($this->UserRepository->addUser($data)) {
            $this->_msg = "添加成功";
            return true;
        } else {
            $this->_code = 402;
            $this->_msg = "添加失败";
            return false;
        }
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
        $data = $this->assembleData($data);
        if ($this->UserRepository->editUser($data)) {
            $this->_msg = "修改成功";
        } else {
            $this->_code = 402;
            $this->_msg = "修改失败";
        }
    }


    public function delUser($id)
    {
        if ($this->UserRepository->delUser($id)) {
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

    public function giftMoney($id, $money)
    {
        DB::beginTransaction();
        try {
            $user = $this->UserRepository->findById($id);
            $data = [
                "user_id" => $id,
                "type" => 8,
                "dq_balance" => $user->balance,
                "wc_balance" => bcadd($user->balance, $money, 2),
                "time" => time(),
                "msg" => "后台赠送" . $user->nickname . $money . "元礼金!",
                "money" => $money
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
}
