<?php

namespace App\Services\Admin;


use App\Repositories\Admin\AdminRepository;
use App\Repositories\Admin\RoleRepository;
use App\Repositories\Admin\UserRepository as Repository;
use App\Repositories\Api\UserRepository;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class GroupUserService extends UserService
{

    /**
     * @var AdminRepository
     */
    private $adminRepository;
    /**
     * @var RoleRepository
     */
    private $roleRepository;

    public function __construct(Repository $userRepository, UserRepository $repository, AdminRepository $adminRepository, RoleRepository $roleRepository)
    {
        parent::__construct($userRepository, $repository);

        $this->adminRepository = $adminRepository;
        $this->roleRepository = $roleRepository;
    }

    const GROUP_LEADER = 1; //组长
    const NOT_GROUP_LEADER = 2; //不是组长

    public function leaderAdd($data)
    {
        if ($this->ApiUserRepository->findByPhone($data["phone"])) {
            $this->_code = 402;
            $this->_msg = "账号已存在";
            return false;
        }
        $data["reg_time"] = time();
        $data["is_customer_service"] = 1;
        $data["code"] = $this->ApiUserRepository->getcode();
        $data["reg_source_id"] = 1;
        $data["password"] = Crypt::encrypt($data["password"]);
        $data['is_group_leader'] = self::GROUP_LEADER;
        $nickname = $data['nickname'] ?? '';
        if (!$nickname) {
            $nickname = $data['nickname'] = "用户" . md5($data["phone"]);
        }
        $data = $this->assembleData($data);
        $insertId = $this->UserRepository->addUser($data);
        if ($insertId) {
            $this->_msg = "添加成功";
            //添加admin记录
            $admin_data = [
                'username' => $nickname,
                'password' => $data["password"],
                'user_id' => $insertId,
                'status' => 2, //下线
                'create_time' => time(),
                'role_id' => $this->roleRepository->getRoleIdByName('员工'),
            ];
            $this->adminRepository->Add_Admin($admin_data);
            return true;
        } else {
            $this->_code = 402;
            $this->_msg = "添加失败";
            return false;
        }
    }

    public function list($data)
    {
        $limit = $data['limit'] ?? 10;
        $offset = (($data['page'] ?? 1) - 1) * $limit;
//        DB::connection()->enableQueryLog();
        $list = $this->UserRepository->findGroupLeaders($this->initSearchWhere($data), $offset, $limit);
        $total = $this->UserRepository->countGroupLeaders($this->initSearchWhere($data));
        $this->_data = ["total" => $total, "list" => $list];
//        $sql = DB::getQueryLog();
//        var_dump($sql);
//        dd($this->_data);
    }

    public function initSearchWhere($data)
    {
        $where = [
            ['is_group_leader', 1],
            'deleted_at' => null
        ];
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

}
