<?php

namespace App\Services\Admin;


use App\Repositories\Admin\AdminRepository;
use App\Repositories\Admin\RoleRepository;
use App\Repositories\Admin\SettingRepository;
use App\Repositories\Admin\UserRepository as Repository;
use App\Repositories\Api\UserRepository;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class GroupUserService extends UserService
{

    const GROUP_LEADER = 1; //组长
    const NOT_GROUP_LEADER = 2; //不是组长

    /**
     * @var AdminRepository
     */
    private $adminRepository;
    /**
     * @var RoleRepository
     */
    private $roleRepository;
    /**
     * @var SettingRepository
     */
    private $settingRepository;

    public function __construct(Repository $userRepository,
                                UserRepository $repository,
                                AdminRepository $adminRepository,
                                RoleRepository $roleRepository,
                                SettingRepository $settingRepository)
    {
        parent::__construct($userRepository, $repository);
        $this->adminRepository = $adminRepository;
        $this->roleRepository = $roleRepository;
        $this->settingRepository = $settingRepository;
    }

    public function leaderAdd($data)
    {
        //获取组长role_id
        $val = $this->settingRepository->getSettingByKey(SettingRepository::GROUP_LEADER_ROLE_KEY);
        if (!$val) {
            $this->_code = 402;
            $this->_msg = "请先设置组长角色ID";
            return false;
        }
        $rol_id = $val->setting_value['role_id'];
        DB::beginTransaction();
        $userData["whats_app_account"] = $data['whats_app_account'] ?? null;
        $userData["whats_app_link"] = $data['whats_app_link'] ?? null;
        $userData["is_customer_service"] = 1;
        $userData["code"] = $this->ApiUserRepository->getcode();
        $userData["reg_source_id"] = 1;
        $userData["phone"] = $data["phone"];
        if (array_key_exists('reg_time', $data)) {
            $userData["reg_time"] = $data["reg_time"];
        } else {
            $userData["reg_time"] = time();
        }
        if (array_key_exists('remarks', $data)) {
            $userData["remarks"] = $data["remarks"];
        }
        if (array_key_exists('is_login', $data)) {
            $userData["is_login"] = $data["is_login"];
        }
        if (array_key_exists('is_transaction', $data)) {
            $userData["is_transaction"] = $data["is_transaction"];
        }
        if (array_key_exists('is_recharge', $data)) {
            $userData["is_recharge"] = $data["is_recharge"];
        }
        if (array_key_exists('is_login', $data)) {
            $userData["is_withdrawal"] = $data["is_withdrawal"];
        }
        $userData["password"] = Crypt::encrypt($data["password"]);
        $userData['is_group_leader'] = self::GROUP_LEADER;
        $userData['nickname'] = $data['nickname'] ?? "用户" . md5($data["phone"]);
        $userData = $this->assembleData($userData);
        $insertId = $this->UserRepository->addUser($userData);
        if ($insertId) {
            //获取组长role_id
            //添加admin记录
            $admin_data = [
                'username' => $data['phone'],
                'password' => $userData["password"],
                'user_id' => $insertId,
                'status' => 2, //下线
                'create_time' => time(),
                'role_id' => $rol_id,
            ];
            $adminRes = $this->adminRepository->Add_Admin($admin_data);
            if ($adminRes) {
                $this->_msg = "添加成功";
                DB::commit();
                return true;
            }
        }
        DB::rollBack();
        $this->_code = 402;
        $this->_msg = "添加失败";
        return false;
    }

    public function list($data)
    {
        $limit = $data['limit'] ?? 10;
        $offset = (($data['page'] ?? 1) - 1) * $limit;
        $list = $this->UserRepository->findGroupLeaders($this->initSearchWhere($data), $offset, $limit);
        $total = $this->UserRepository->countGroupLeaders($this->initSearchWhere($data));
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function initSearchWhere($data)
    {
        $where = [];
        if (isset($data['id']) && $data['id']) {
            $where[] = ['id', $data['id']];
        }
        if (isset($data['phone']) && $data['phone']) {
            $where[] = ['phone', $data['phone']];
        }
        if (isset($data['nickname']) && $data['nickname']) {
            $where[] = ['nickname', 'like', $data['nickname']];
        }
        return $where;
    }

    public function logicDel($id)
    {
        DB::beginTransaction();
        try {
            $userRes = $this->UserRepository->logicDel($id);
            $adminRes = $this->adminRepository->logicDelByUserId($id);
            if ($userRes && $adminRes) {
                DB::commit();
            } else {
                DB::rollBack();
                $this->_code = 402;
                $this->_msg = "操作失败";
            }

        } catch (\Exception $exception) {
            DB::rollBack();
            $this->_code = 402;
            $this->_msg = "操作失败";
        }
    }

    public function searchAccount($phone)
    {
        $account = $this->_data = $this->UserRepository->searchAccount($phone);
        if ($account) {
            $this->_data = $account->toArray();
            return;
        }
        $this->_code = 402;
        $this->_msg = '用户不存在';
    }

    public function bindAccount()
    {
        $data = [
            'user_id' => $this->intInput('user_id'),
            'username' => $this->strInput('account'),
            'nickname' => $this->strInput('nickname'),
            'status' => 2,
            'create_time' => time(),
            'password' => Crypt::encrypt($this->strInput('password'))
        ];
        ##判断用户是否已绑定
        if ($this->adminRepository->Check_Bind($data['user_id'])) {
            $this->_code = 402;
            $this->_msg = "该账号已绑定管理员账号";
            return false;
        }
        $val = $this->settingRepository->getSettingByKey(SettingRepository::GROUP_LEADER_ROLE_KEY);
        if (!$val) {
            $this->_code = 402;
            $this->_msg = "请先设置组长角色ID";
            return false;
        }
        $rol_id = $val->setting_value['role_id'];
        $data['role_id'] = $rol_id;
        DB::beginTransaction();
        ##绑定
        $userRes = $this->UserRepository->editUser([
            'id' => $this->intInput('user_id'),
            'is_group_leader' => 1
        ]);
        $adminRes = $this->adminRepository->addAdmin($data);
        if (!$adminRes || !$userRes) {
            $this->_code = 402;
            $this->_msg = '绑定失败';
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->_msg = '绑定成功';
        return true;
    }

}
