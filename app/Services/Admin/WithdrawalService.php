<?php


namespace App\Services\Admin;


use App\Dictionary\SettingDic;
use App\Jobs\Withdraw_Call;
use App\Repositories\Admin\SettingRepository;
use App\Repositories\Admin\UserRepository;
use App\Repositories\Admin\WithdrawalRepository;
use App\Services\BaseService;
use App\Services\Pay\PayContext;
use App\Services\Pay\PayStrategy;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function Symfony\Component\VarDumper\Dumper\esc;

class WithdrawalService extends BaseService
{
    private $WithdrawalRepository, $UserRepository, $SettingRepository;
    private $payContext;

    public function __construct
    (
        WithdrawalRepository $withdrawalRepository,
        UserRepository $userRepository,
        PayContext $payContext,
        SettingRepository $settingRepository
    )
    {
        $this->WithdrawalRepository = $withdrawalRepository;
        $this->UserRepository = $userRepository;
        $this->payContext = $payContext;
        $this->SettingRepository = $settingRepository;
    }

    public function findAll($page, $limit, $status)
    {
        $list = $this->WithdrawalRepository->findAll(($page - 1) * $limit, $limit, $status);
        $total = $this->WithdrawalRepository->countAll($status);
        ##提现风险配置
        $config = $this->SettingRepository->getSettingValueByKey(SettingDic::key('WITHDRAW_SAFE'));
        $limit = 0;
        if($config){
            $limit = $config['limit'] ?? 0;
        }
        $this->_data = ["total" => $total, "list" => $list, 'limit'=>$limit];
    }

    /**
     * 审核
     */
    public function auditRecord($request)
    {
        $data = $request->post();
        $withdrawalRecord = $this->WithdrawalRepository->findById($data["id"]);
        if($withdrawalRecord->status != 0){
            $this->_code = 414;
            $this->_msg = '订单已审核';
            return false;
        }

        if ($data["status"] == 1) {
            $password = $data['password'] ?? '';
            if(!$this->withdrawSafeCheck($withdrawalRecord->money, $password))
            {
                return false;
            }

//            if ($data["type"] == 1) {
//                $this->changeAgencyCommission($data["id"]);
//                unset($data["type"]);
//            }
//            else {
//                $this->addWithdrawalLogs($data["id"]);
//            }
            if($withdrawalRecord->message){
                $this->_code = 414;
                $this->_msg = $withdrawalRecord->message;
                return false;
            }
            $host = $request->getHost();    // 根据api接口host判断是来源于哪个客户；用什么支付方式 //  $host = "api.999666.in"; 变成 999666.in
            if (count(explode('.', $host)) == 3) {
                $host = substr(strstr($host, '.'), 1);
            }
            if($withdrawalRecord->with_type){
                $payProvide = $withdrawalRecord->with_type;
            }else{
                if (!isset(PayContext::$pay_provider[$host])) {
                    $this->_msg = 'not find strategy';
                    return false;
                }
                $payProvide = PayContext::$pay_provider[$host];
            }

            $strategyClass = $this->payContext->getStrategy($payProvide);  // 获取支付公司类
            if(!$strategyClass){
                $this->_code = 414;
                $this->_msg = "Payment method not configured";
                return false;
            }
            $result = $strategyClass->withdrawalOrder($withdrawalRecord);
            if (!$result) {
                $this->_code = 414;
                $this->_msg = $strategyClass->_msg;
                $this->WithdrawalRepository->editRecord(['id'=>$data["id"], 'message'=>$strategyClass->_msg]);
                return false;
            }
            $data['pltf_order_no'] = $result['pltf_order_no']??'';
//            $data['order_no'] = $request['order_no'];
        } elseif ($data["status"] == 2) {  // 如果审核不通过，将冻结金额返还
            $user = $this->UserRepository->findById($withdrawalRecord->user_id);
            $type = $withdrawalRecord->type;  //  0:用户提现 1:代理佣金提现
            if ($data["type"] == 1) {
                $user->freeze_agent_money = bcsub($user->freeze_agent_money, $withdrawalRecord->money, 2);
                $user->commission = bcadd($user->commission, $withdrawalRecord->money, 2);
            } else {
                $cur_balance = bcadd($user->balance,$withdrawalRecord->money,2);
                ##增加用户余额变化记录
                $this->UserRepository->addBalanceLog($withdrawalRecord->user_id, $withdrawalRecord->money,11,'用户提现驳回', $user->balance, $cur_balance);

                $user->freeze_money = bcsub($user->freeze_money, $withdrawalRecord->money, 2);
                $user->balance = bcadd($user->balance, $withdrawalRecord->money, 2);
            }
//            DB::connection()->enableQueryLog();
            $user->save();
//            $sql = DB::getQueryLog();
//            Log::channel('kidebug')->info('withdraw', $sql);
        }
//        $data["loan_time"] = time();
        $data["approval_time"] = time();
        if ($this->WithdrawalRepository->editRecord($data)) {
            $this->_msg = "审核通过";
            return true;
        } else {
            $this->_code = 402;
            $this->_msg = "审核失败";
            return false;
        }
    }

    public function retry()
    {
        $data = request()->post();
        $withdrawalRecord = $this->WithdrawalRepository->findById($data["id"]);
        $order_no = PayStrategy::onlyosn();
        $withdrawalRecord->order_no = $order_no;
        if($withdrawalRecord->status != 1){
            $this->_code = 414;
            $this->_msg = '订单不支持重新提交代付操作';
            return false;
        }
        if($withdrawalRecord->pay_status != 3){
            $this->_code = 414;
            $this->_msg = '订单不支持重新提交代付操作';
            return false;
        }
        $password = $data['password'] ?? '';
        if(!$this->withdrawSafeCheck($withdrawalRecord->money, $password))
        {
            return false;
        }

        $payProvide = $withdrawalRecord->with_type;
        $strategyClass = $this->payContext->getStrategy($payProvide);  // 获取支付公司类
        if(!$strategyClass){
            $this->_code = 414;
            $this->_msg = "Payment method not configured";
            return false;
        }
        $result = $strategyClass->withdrawalOrder($withdrawalRecord);
        if (!$result) {
            $this->_code = 414;
            $this->_msg = $strategyClass->_msg;
            return false;
        }
        $update['id'] = $data["id"];
        $update['pltf_order_no'] = $result['pltf_order_no']??'';
        $update['order_no'] = $order_no;
        $update["approval_time"] = time();
        $update["pay_status"] = 0;
        if ($this->WithdrawalRepository->editRecord($update)) {
            $this->_msg = "重新提交成功";
            return true;
        } else {
            $this->_code = 402;
            $this->_msg = "重新提交失败";
            return false;
        }
    }

    public function batchPassRecord($data)
    {
        $ids = array_column($data['ids'],'id');
        $records = $this->WithdrawalRepository->findAllByIds($ids);
        $ids2 = [];
        foreach ($records as $record) {
            if($record['status'] == 0){
                if(!$this->withdrawSafeCheck($record['money'],''))
                {
                    continue;
                }
                if ($record["type"] == 1) {
                    $this->changeAgencyCommission($record["id"]);
                } else {
//                $this->addWithdrawalLogs($record["id"]);

                }
                $ids2[] = $record['id'];
            }
        }
        if ($this->WithdrawalRepository->batchUpdateRecord($ids2, 1)) {
            foreach($ids2 as $item){
                $this->addWithdrawQueue($item);
                sleep(3);
            }
            $this->_msg = "审核成功";
        } else {
            $this->_code = 402;
            $this->_msg = "审核失败";
        }
    }

    public function batchFailureRecord($data)
    {
        $ids = array_column($data["ids"],'id');
        DB::beginTransaction();
        try{
            ##修改提现记录
            $res = $this->WithdrawalRepository->batchUpdateRecord($ids, 2, $data["message"]);
            if (!$res)throw new \Exception('审核失败');
            ##返还余额
            $withdrawalRecords = $this->WithdrawalRepository->findByIds($ids);
            foreach($withdrawalRecords as $withdrawalRecord){
                $user_id = $withdrawalRecord['user_id'];
                $user = $this->UserRepository->findById($user_id);
                $money = $withdrawalRecord['money'];
                $dq_balance = $user->balance;
                $wc_balance = bcadd($dq_balance, $money,2);
                ##增加用户余额变化记录
                $this->UserRepository->addBalanceLog($user_id, $money,11,'用户提现驳回', $dq_balance, $wc_balance);
                $freeze_money = bcsub($user->freeze_money, $money, 2);
                ##更新用户数据
                $res = $this->UserRepository->editUser(["id" => $user_id, "balance" => $wc_balance, 'freeze_money'=>$freeze_money]);
                if($res === false)
                    throw new \Exception('余额返还失败');
            }
            DB::commit();
        }catch(\Exception $e){
            $this->_code = 402;
            $this->_msg = $e->getMessage();
            DB::rollBack();
        }



    }

    public function changeAgencyCommission($id)
    {
        $record = $this->WithdrawalRepository->findById($id);
        $user = $this->UserRepository->findById($record->user_id);
        $userUpdate = ["id" => $user->id, "commission" => $user->commission - $record->payment, "cl_commission" => $user->cl_commission + $record->payment];
        $this->UserRepository->editUser($userUpdate);
        $data = [
            "user_id" => $user->id,
            "dq_commission" => $user->commission,
            "wc_commission" => $user->commission - $record->payment,
            "time" => time(),
            "order_no" => $record->order_no,
            "phone" => $user->phone,
            "nickname" => $user->nickname,
            "message" => $user->nickname . "提现佣金" . $record->payment . "成功！"
        ];
        $this->WithdrawalRepository->addCommissionLogs($data);
    }

    public function addWithdrawalLogs($id)
    {
        $record = $this->WithdrawalRepository->findById($id);
        $user = $this->UserRepository->findById($record->user_id);
        $userUpdate = ["id" => $user->id, "balance" => $user->balance - $record->payment, "cl_withdrawal" => $user->cl_withdrawal + $record->payment];
        $this->UserRepository->editUser($userUpdate);
        $insert = [
            "user_id" => $user->id,
            "type" => 3,
            "dq_balance" => $user->balance,
            "wc_balance" => $user->balance - $record->payment,
            "time" => time(),
            "msg" => $user->nickname . "提现" . $record->payment . "成功!"
        ];
        $this->WithdrawalRepository->addBalanceLogs($insert);
    }

    public function addWithdrawQueue($id)
    {
        Withdraw_Call::dispatch($id)->onQueue('Withdraw_Queue');;
    }

    public function searchRecord($data)
    {
        if(isset($data['conditions']['status'])){
            if($data['conditions']['status'] == -1){
                unset($data['conditions']['status']);
            }elseif($data['conditions']['status'] == 1){
                if(isset($data['conditions']['pay_status']) && ($data['conditions']['pay_status'] == ""))
                    unset($data['conditions']['pay_status']);
            }
        }
        $data = $this->getUserIds($data, "user_id");
        $list = $this->WithdrawalRepository->searchRecord($data, ($data["page"] - 1) * $data["limit"], $data["limit"]);
        $total = $this->WithdrawalRepository->countSearchRecord($data);
        $this->_data = ["total" => $total, "list" => $list];
    }

    /**
     * 获取最新的一条待审核的提现
     */
    public function getNewest()
    {
        $result = $this->WithdrawalRepository->getNewest();
        if (!$result) {
            return ['create_time' => 0, 'id' => 0];
        }
        return $result;
    }

    /**
     * 获取最新的10条待审核的提现
     */
    public function getNewests()
    {
        return $this->WithdrawalRepository->getNewests();
    }

    public function cancellationRefund($id)
    {
        $message = $this->strInput('message');
        $withdrawal = $this->WithdrawalRepository->findById($id);
        if ($withdrawal->status !== 1) {
            if ($withdrawal->pay_status !== 0) {
                $this->_msg = "记录状态必须是通过且第三方为支付";
                $this->_code = 402;
                return;
            } else {
                $this->_msg = "记录状态必须是通过";
                $this->_code = 402;
                return;
            }
        }
        $updateWithdrawal = ["id" => $id, "status" => 2, "message" => $message];
        DB::beginTransaction();
        try{
            $withdrawalResult = $this->WithdrawalRepository->editRecord($updateWithdrawal);
            $user = $this->UserRepository->findById($withdrawal->user_id);
            if ($user->freeze_money < $withdrawal->money)
                throw new \Exception('提现冻结金额小于提现金额');
            $this->UserRepository->addBalanceLog($user->id, $withdrawal->money,11,'后台取消用户提现', $user->balance, bcadd($user->balance, $withdrawal->money, 2));
            $updateUser = ["id" => $user->id, "freeze_money" => bcsub($user->freeze_money, $withdrawal->money, 2), "balance" => bcadd($user->balance, $withdrawal->money, 2)];
            $userResult = $this->UserRepository->editUser($updateUser);
            if ($withdrawalResult && $userResult) {
                $this->_msg = "退款成功";
            } else {
                throw new \Exception('退款失败');
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            $this->_msg = $e->getMessage();
            $this->_code = 402;
        }


    }

    public function withdrawSafeCheck($money, $password): bool
    {
        ##审核风险检测
        $conf = $this->SettingRepository->getSettingValueByKey(SettingDic::key('WITHDRAW_SAFE'));
        if($conf && isset($conf['limit']) && $conf['limit'] > 0)
        {
            if($money >= $conf['limit'])
            {
                if(Crypt::decrypt($conf['password']) != $password)
                {
                    $this->_code = 414;
                    $this->_msg = '提现金额达到风险提现金额,请输入提现密码或者联系超管审核';
                    return false;
                }
            }
        }
        return true;
    }

}
