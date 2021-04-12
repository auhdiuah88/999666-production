<?php


namespace App\Services\Admin;


use App\Repositories\Admin\BankRepository;
use App\Services\BaseService;

class BankService extends BaseService
{
    private $BankRepository;

    public function __construct(BankRepository $repository)
    {
        $this->BankRepository = $repository;
    }

    public function findAll($page, $limit)
    {
        $list = $this->BankRepository->findAll(($page - 1) * $limit, $limit);
        $total = $this->BankRepository->countAll();
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function findById($id)
    {
        $this->_data = $this->BankRepository->findById($id);
    }

    public function addBank($data)
    {
        if ($this->BankRepository->addBank($data)) {
            $this->_msg = "添加成功";
        } else {
            $this->_code = 402;
            $this->_msg = "添加失败";
        }
    }

    public function editBank($data)
    {
        if ($this->BankRepository->editBank($data)) {
            $this->_msg = "编辑成功";
        } else {
            $this->_code = 402;
            $this->_msg = "编辑失败";
        }
    }

    public function delBank($id)
    {
        if ($this->BankRepository->delBank($id)) {
            $this->_msg = "删除成功";
        } else {
            $this->_code = 402;
            $this->_msg = "删除失败";
        }
    }

    public function searchBank($phone, $account_holder, $page, $limit)
    {
        $user_id = 0;
        if($phone){
            $user = $this->BankRepository->getBankUserIds($phone);
            if (empty($user)) {
                return;
            }
            $user_id = $user->id;
        }
        $list = $this->BankRepository->findBankByUserId($user_id,$account_holder,($page - 1) * $limit, $limit);
        $total = $this->BankRepository->countBankByUserId($user_id,$account_holder);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
