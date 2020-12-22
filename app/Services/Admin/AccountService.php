<?php


namespace App\Services\Admin;


use App\Repositories\Admin\AccountRepository;
use App\Repositories\Admin\SettingRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class AccountService extends BaseService
{
    private $AccountRepository, $SettingRepository;

    public function __construct(AccountRepository $accountRepository, SettingRepository $settingRepository)
    {
        $this->AccountRepository = $accountRepository;
        $this->SettingRepository = $settingRepository;
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
        $agent_role = $this->SettingRepository->getStaff();
        if(!$agent_role){
            $this->_code = 402;
            $this->_msg = "请先设置代理员工角色";
            return false;
        }
        $role_id = $agent_role['setting_value']['role_id'];
        $data["is_customer_service"] = 1;
        $data["reg_time"] = time();
        $password = $data["password"];
        $data["password"] = Crypt::encrypt($data["password"]);
        if (!array_key_exists("nickname", $data)) {
            $data["nickname"] = "用户" . md5($data["phone"]);
        } elseif (!$data["nickname"]) {
            $data["nickname"] = "用户" . md5($data["phone"]);
        }
        $data["reg_source_id"] = 1;
        $data["is_login"] = 1;
        $data["is_transaction"] = 1;
        $data["is_recharge"] = 1;
        $data["is_withdrawal"] = 1;
        $data["is_withdrawal"] = 1;
        $data["code"] = $this->AccountRepository->getCode();
        DB::beginTransaction();
        try{
            ##增加代理用户账号
            if (!$user_id = $this->AccountRepository->addAccount($data)) {
                throw new \Exception("代理用户账号添加失败");
            }
            ##增加管理员账号
            $admin_data = [
                'username' => $data["phone"],
                'nickname' => $data["nickname"],
                'password' => $data["password"],
                'status' => 2,
                'role_id' => $role_id,
                'user_id' => $user_id
            ];
            $res = $this->AccountRepository->addAdmin($admin_data);
            if($res === false){
                throw new \Exception("代理用户管理员账号添加失败");
            }
            DB::commit();
            $this->_msg = '代理员工账号创建成功';
            $this->_data = [
                'account' => $admin_data['username'],
                'password' => $password,
            ];
            return true;
        }catch(\Exception $e){
            $this->_msg = $e->getMessage();
            $this->_code = 402;
            DB::rollBack();
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
        $list = $this->AccountRepository->searchAccount($data, ($data["page"] - 1) * $data["limit"], $data["limit"]);
        $total = $this->AccountRepository->countSearchAccount($data);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
