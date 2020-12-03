<?php


namespace App\Services\Admin;


use App\Repositories\Admin\AccountRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Crypt;

class AccountService extends BaseService
{
    private $AccountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->AccountRepository = $accountRepository;
    }

    public function findAll($page, $limit)
    {
        $list = $this->AccountRepository->findAll(($page - 1) * $limit, $limit);
        $total = $this->AccountRepository->countAll();
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function findById($id)
    {
        $this->_data = $this->AccountRepository->findById($id);
    }

    public function addAccount($data)
    {
        if ($this->AccountRepository->findByPhone($data["phone"])) {
            $this->_code = 402;
            $this->_msg = "账号已存在";
            return false;
        }
        $data["is_customer_service"] = 1;
        $data["reg_time"] = time();
        $data["password"] = Crypt::encrypt($data["password"]);
        if (!array_key_exists("nickname", $data)) {
            $data["nickname"] = "用户" . md5($data["phone"]);
        }
        $data["reg_source_id"] = 1;
        $data["is_login"] = 1;
        $data["is_transaction"] = 1;
        $data["is_recharge"] = 1;
        $data["is_withdrawal"] = 1;
        $data["is_withdrawal"] = 1;
        $data["code"] = $this->AccountRepository->getCode();
        if ($this->AccountRepository->addAccount($data)) {
            $this->_msg = "添加成功";
            return true;
        } else {
            $this->_code = 402;
            $this->_msg = "添加失败";
            return false;
        }
    }

    public function editAccount($data)
    {
        if (array_key_exists("password", $data)) {
            $data["password"] = Crypt::encrypt($data["password"]);
        }
        if ($this->AccountRepository->editAccount($data)) {
            $this->_msg = "编辑成功";
        } else {
            $this->_code = 402;
            $this->_msg = "编辑失败";
        }
    }

    public function delAccount($id)
    {
        if ($this->AccountRepository->delAccount($id)) {
            $this->_msg = "删除成功";
        } else {
            $this->_code = 402;
            $this->_msg = "删除失败";
        }
    }

    public function searchAccount($data)
    {
        $where = [];
        if (array_key_exists("phone", $data)) {
            $where["phone"] = $data["phone"];
        }
        if (array_key_exists("nickname", $data)) {
            $where["nickname"] = $data["nickname"];
        }
        $list = $this->AccountRepository->searchAccount($where, ($data["page"] - 1) * $data["limit"], $data["limit"]);
        $total = $this->AccountRepository->countSearchAccount($where);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
