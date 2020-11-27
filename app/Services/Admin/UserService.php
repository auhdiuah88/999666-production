<?php


namespace App\Services\Admin;


use App\Repositories\Admin\UserRepository as Repository;
use App\Repositories\Api\UserRepository;
use App\Services\BaseService;

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
        $data["reg_time"] = time();
        $data["code"] = $this->ApiUserRepository->getcode();
        $data = $this->assembleData($data);
        if ($this->UserRepository->addUser($data)) {
            $this->_msg = "添加成功";
        } else {
            $this->_code = 402;
            $this->_msg = "添加失败";
        }
    }

    public function editUser($data)
    {
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
        $one = $this->UserRepository->findById($data["one_recommend_id"]);
        $two = $this->UserRepository->findById($data["two_recommend_id"]);
        $data["one_recommend_phone"] = $one->phone;
        $data["two_recommend_phone"] = $two->phone;
        return $data;
    }

    public function getCustomerService()
    {
        $this->_data = $this->UserRepository->getCustomerService();
    }

    public function modifyCustomerService($ids, $customer_id)
    {
        if ($this->UserRepository->modifyCustomerService($ids, $customer_id)) {
            $this->_msg = "修改客服成功";
        } else {
            $this->_code = 402;
            $this->_msg = "修改客服失败";
        }
    }
}
