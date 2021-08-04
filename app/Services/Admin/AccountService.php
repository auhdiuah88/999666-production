<?php


namespace App\Services\Admin;


use App\Repositories\Admin\AccountRepository;
use App\Repositories\Admin\AdminRepository;
use App\Repositories\Admin\agent\AgentDataRepository;
use App\Repositories\Admin\SettingRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class AccountService extends BaseService
{
    private $AccountRepository, $SettingRepository, $AdminRepository, $AgentDataRepository;

    public function __construct
    (
        AccountRepository $accountRepository,
        SettingRepository $settingRepository,
        AdminRepository $adminRepository,
        AgentDataRepository $agentDataRepository
    )
    {
        $this->AccountRepository = $accountRepository;
        $this->SettingRepository = $settingRepository;
        $this->AdminRepository = $adminRepository;
        $this->AgentDataRepository = $agentDataRepository;
    }

    public function findAll($page, $limit)
    {
        $admin_id = request()->get('admin_id');
        $admin = $this->AdminRepository->getAdminUserById($admin_id);
        $list = $this->AccountRepository->findAll(($page - 1) * $limit, $limit, $admin['user_id']);
        $total = $this->AccountRepository->countAll($admin['user_id']);
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function findById($id)
    {
        $admin_id = request()->get('admin_id');
        $admin = $this->AdminRepository->getAdminUserById($admin_id);
        $this->_data = $this->AccountRepository->findById($id, $admin['user_id']);
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
        $admin_id = request()->get('admin_id');
        $admin = $this->AdminRepository->getAdminUserById($admin_id);
        if($admin['user']){
            $data["invite_relation"] = makeInviteRelation($admin['user']['invite_relation'], $admin['user']['id']);
        }

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
        if ($this->AccountRepository->editAccount($data) !== false) {
            $this->_msg = "编辑成功";
        } else {
            $this->_code = 402;
            $this->_msg = "编辑失败";
        }
    }

    public function bindAccount(){
        $data = [
            'user_id' => $this->intInput('user_id'),
            'username' => $this->strInput('account'),
            'nickname' => $this->strInput('nickname'),
            'status' => 1,
            'password' => Crypt::encrypt($this->strInput('password'))
        ];
        ##判断用户是否已绑定
        if($this->AdminRepository->Check_Bind($data['user_id'])){
            $this->_code = 402;
            $this->_msg = "该账号已绑定管理员账号";
            return false;
        }
        $agent_role = $this->SettingRepository->getStaff();
        if(!$agent_role){
            $this->_code = 402;
            $this->_msg = "请先设置代理员工角色";
            return false;
        }
        $data['role_id'] = $agent_role['setting_value']['role_id'];

        $admin_id = request()->get('admin_id');
        $admin = $this->AdminRepository->getAdminUserById($admin_id);
        $invite_relation = makeInviteRelation($admin['user']['invite_relation'], $admin['user']['id']);
        DB::beginTransaction();
        try{
            ##增加后台管理账号
            if(!$this->AccountRepository->addAdmin($data))
                throw new \Exception('增加员工管理账号失败');
            ##修改邀请关系
            $res = $this->AccountRepository->editInviteRelation($data['user_id'], $invite_relation);
            if($res === false)
                throw new \Exception('员工关联关系绑定失败');
            ##修改员工下面的用户的邀请关系
            $this->AccountRepository->editUserInviteRelation($data['user_id'], $invite_relation);

            DB::commit();
            $this->_msg = '绑定成功';
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            $this->_code = 402;
            $this->_msg = $e->getMessage();
            return false;
        }

    }

    public function delAccount($id)
    {
        DB::beginTransaction();
        try{
            $res = $this->AccountRepository->delAccount($id);
            if($res === false)
                throw new \Exception('员工h5账号删除失败');
            $res = $this->AdminRepository->delByUserId($id);
            if($res === false)
                throw new \Exception('员工管理员账号删除失败');
            DB::commit();
            $this->_msg = '操作成功';
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            $this->_msg = $e->getMessage();
            $this->_code = 402;
            return false;
        }
    }

    public function searchAccount($data)
    {
        $admin_id = request()->get('admin_id');
        $admin = $this->AdminRepository->getAdminUserById($admin_id);
        $list = $this->AccountRepository->searchAccount($data, ($data["page"] - 1) * $data["limit"], $data["limit"], $admin['user_id']);
        $total = $this->AccountRepository->countSearchAccount($data, $admin['user_id']);
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function showData(){
        $user_id = $this->intInput('user_id');
        $sourceType = $this->intInput('sourceType');
        $this->AgentDataRepository->user_id = $user_id;
        $this->AgentDataRepository->user_ids = $this->AgentDataRepository->getSourceTypeUserIds($sourceType);

        $start_time = $this->intInput('start_time');
        $end_time = $this->intInput('end_time');
        if($start_time && $end_time){
            $time_map = [$start_time, $end_time];
        }else{
            $time_map = [];
        }
        $this->AgentDataRepository->time_map = $time_map;

        #会员统计
        ##会员总数
        $member_total = $this->AgentDataRepository->getMemberTotal();
        ##新增会员
        $new_member_num = $this->AgentDataRepository->getNewMemberNum();
        ##活跃人数[这段时间有下过注的人]
        $active_member_num = $this->AgentDataRepository->getActiveMemberNum();
        ##首充人数
        $first_recharge_num = $this->AgentDataRepository->getFirstRechargeNum();
        $member_data = compact('member_total','new_member_num','active_member_num','first_recharge_num');

        #出入金额汇总
        ##充值金额
        $recharge_money = $this->AgentDataRepository->getRechargeMoney();
        ##已提现金额
        $success_withdraw_money = $this->AgentDataRepository->getSuccessWithDrawMoney();
        ##待审核提现金额
        $wait_withdraw_money = $this->AgentDataRepository->getWaitWithdrawMoney();
        ##用户余额[包含余额和佣金]
        $balance_commission = $this->AgentDataRepository->getBalanceCommission();
        ##订单分佣
        $commission_money = $this->AgentDataRepository->getCommissionMoney();
        ##购买签到礼包金额
        $sign_money = $this->AgentDataRepository->getSignMoney();
        ##签到礼包领取
        $receive_sign_money = $this->AgentDataRepository->getReceiveSIgnMoney();
        ##赠金
        $giveMoney = $this->AgentDataRepository->getGiveMoney();
        $money_data = compact('recharge_money','success_withdraw_money','wait_withdraw_money','balance_commission','commission_money','sign_money','receive_sign_money','giveMoney');

        #订单汇总
        ##订单数
        $order_num = $this->AgentDataRepository->getOrderNum();
        ##下单金额
        $order_money = $this->AgentDataRepository->getOrderMoney();
        ##用户投注盈利[只是单纯赢的钱]
        $order_win_money = $this->AgentDataRepository->getOrderWinMoney();
        ##服务费[代理赚到的服务费]
        $service_money = $this->AgentDataRepository->getServiceMoney();
        $order_data = compact('order_num','order_money','order_win_money','service_money');

        $this->_data = compact('member_data','money_data','order_data');
        return true;
    }

    public function frozenAccount()
    {
        $user_id = $this->intInput('user_id');
        DB::beginTransaction();
        try{
            ##冻结用户h5账号
            $res = $this->AccountRepository->frozen($user_id);
            if($res === false)throw new \Exception("冻结员工h5账号失败");
            ##冻结用户admin账号
            $res = $this->AdminRepository->frozenByUserId($user_id);
            if($res === false)throw new \Exception("冻结员工管理员账号失败");
            DB::commit();
            $this->_msg = "操作成功";
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            $this->_code = 402;
            $this->_msg = $e->getMessage();
            return false;
        }
    }

    public function disFrozenAccount()
    {
        $user_id = $this->intInput('user_id');
        DB::beginTransaction();
        try{
            ##解冻用户h5账号
            $res = $this->AccountRepository->disFrozen($user_id);
            if($res === false)throw new \Exception("解冻员工h5账号失败");
            ##解冻用户admin账号
            $res = $this->AdminRepository->disFrozenByUserId($user_id);
            if($res === false)throw new \Exception("解冻员工管理员账号失败");
            DB::commit();
            $this->_msg = "操作成功";
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            $this->_code = 402;
            $this->_msg = $e->getMessage();
            return false;
        }
    }

    public function editAdminUser()
    {
        $user_id = $this->intInput('user_id');
        $admin_id = $this->intInput('admin_id');
        $user_data = [
            'whats_app_account' => $this->strInput('whats_app_account'),
            'whats_app_link' => $this->strInput('whats_app_link'),
        ];
        $admin_data = [
            'username' => $this->strInput('username'),
        ];
        $password = $this->strInput('password');
        if($password)
            $admin_data['password'] = Crypt::encrypt($password);
        DB::beginTransaction();
        try{
            ##修改用户what_app
            $res = $this->AccountRepository->editAdminUser($user_id, $user_data);
            if($res === false)throw new \Exception("修改员工h5账号失败");
            ##修改用户admin账号
            $res = $this->AdminRepository->editAdminUser($admin_id, $admin_data);
            if($res === false)throw new \Exception("修改员工管理员账号失败");
            DB::commit();
            $this->_msg = "操作成功";
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            $this->_code = 402;
            $this->_msg = $e->getMessage();
            return false;
        }
    }

}
