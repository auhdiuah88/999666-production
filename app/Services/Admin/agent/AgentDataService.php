<?php


namespace App\Services\Admin\agent;


use App\Repositories\Admin\agent\AgentDataRepository;
use App\Repositories\Admin\agent\AgentUserRepository;

class AgentDataService extends BaseAgentService
{

    protected $AgentDataRepository, $AgentUserRepository;

    public function __construct(AgentUserRepository $agentUserRepository, AgentDataRepository $agentDataRepository){
        $this->AgentDataRepository = $agentDataRepository;
        $this->AgentUserRepository = $agentUserRepository;
    }

    public function agentIndexData(){
        $this->getAdmin();
        $this->AgentDataRepository->user_id = $this->admin->user_id;
        $this->AgentDataRepository->user_ids = $this->AgentDataRepository->getUserIds();

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
        ##线下银行卡充值金额
        $bankcard_recharge_money = $this->AgentDataRepository->getBankCardRechargeMoney();
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
        $money_data = compact('recharge_money','success_withdraw_money','wait_withdraw_money','balance_commission','commission_money','sign_money','receive_sign_money','giveMoney','bankcard_recharge_money');

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

    public function getIndexData()
    {
        $this->getAdmin();
        $this->AgentDataRepository->user_id = $this->admin->user_id;
        $this->AgentDataRepository->user_ids = $this->AgentDataRepository->getUserIds();

        $start_time = $this->intInput('start_time');
        $end_time = $this->intInput('end_time');
        if($start_time && $end_time){
            $time_map = [$start_time, $end_time];
        }else{
            $time_map = [];
        }
        if($time_map && $time_map[1] - $time_map[0] >= 5 * 24 * 60 * 60){
            $this->_code = 402;
            $this->_msg = "时间范围只能是连续的5天";
            return false;
        }
        $this->AgentDataRepository->time_map = $time_map;

        $flag = $this->intInput("flag",1);
        switch ($flag){
            case 1:
                $data = $this->getUserData();
                break;
            case 2:
                $data = $this->getFinancialData();
                break;
            default:
                $data = $this->getOrderData();
                break;
        }
        $this->_data = $data;
        return true;
    }

    protected function getUserData()
    {
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
        return $member_data;
    }

    protected function getFinancialData()
    {
        #出入金额汇总
        ##充值金额
        $recharge_money = $this->AgentDataRepository->getRechargeMoney();
        ##线下银行卡充值金额
        $bankcard_recharge_money = $this->AgentDataRepository->getBankCardRechargeMoney();
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
        $money_data = compact('recharge_money','success_withdraw_money','wait_withdraw_money','balance_commission','commission_money','sign_money','receive_sign_money','giveMoney','bankcard_recharge_money');
        return $money_data;
    }

    protected function getOrderData()
    {
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
        return $order_data;
    }

    public function inviteInfo()
    {
        $this->getAdmin();
        $user = $this->AgentUserRepository->getInviteInfo($this->admin->user_id);
        if(!$user){
            $this->_msg = '请先绑定员工帐号';
            $this->_code = 403;
            return false;
        }
        $code = $user->code;
        ## https://goshop6.in/?code=AAAAAA#/pages/register/register
        $url = env('SHARE_URL','') . "/?code={$code}#/pages/register/register";
        $this->_data = compact('code','url');
        return true;
    }

}
