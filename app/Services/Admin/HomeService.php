<?php


namespace App\Services\Admin;


use App\Repositories\Admin\HomeRepository;
use App\Services\BaseService;

class HomeService extends BaseService
{
    private $HomeRepository;

    public function __construct(HomeRepository $homeRepository)
    {
        $this->HomeRepository = $homeRepository;
    }

    public function findAll()
    {
        $timeMap = [strtotime(date("Y-m-d 00:00:00")), strtotime(date("Y-m-d 23:59:59"))];
        $this->_data = $this->getContext($timeMap);
    }

    public function searchHome($timeMap)
    {
        $this->_data = $this->getContext($timeMap);
    }

    public function getContext($timeMap)
    {
        $item = new \stdClass();
        // 会员数
        $item->members = $this->HomeRepository->countMembers();
        // 新增会员
        $item->newMembers = $this->HomeRepository->countNewMembers($timeMap);
        // 普通新增会员
        $item->ordinaryMembers = $this->HomeRepository->countOrdinaryMembers($timeMap);
        // 代理裂变会员
        $item->agentMembers = $this->HomeRepository->countAgentMembers($timeMap);
        // 红包裂变会员
        $item->envelopeMembers = $this->HomeRepository->countEnvelopeMembers($timeMap);
        // 活跃人数
        $item->activePeopleNumber = $this->HomeRepository->countActivePeopleNumber($timeMap);
        // 首充人数
        $item->firstChargeNumber = $this->HomeRepository->countFirstChargeNumber($timeMap);
        // 普通首充
        $item->ordinaryFirstChargeNumber = $this->HomeRepository->countOrdinaryFirstChargeNumber($timeMap);
        // 代理首充
        $item->agentFirstChargeNumber = $this->HomeRepository->countAgentFirstChargeNumber($timeMap);
        // 充值金额
        $item->rechargeMoney = $this->HomeRepository->sumRechargeMoney($this->HomeRepository->getIds(), $timeMap);
        // 提现金额
        $item->withdrawalMoney = $this->HomeRepository->sumWithdrawalMoney($this->HomeRepository->getIds(), $timeMap);
        // 待提现金额
        $item->toBeWithdrawalMoney = $this->HomeRepository->sumUserBalance() + $this->HomeRepository->sumUserCommission();
        // 订单分佣
        $item->subCommission = $this->HomeRepository->sumSubCommission($this->HomeRepository->getIds(), $timeMap);
        // 赠金
        $item->giveMoney = $this->HomeRepository->sumGiveMoney($this->HomeRepository->getIds(), $timeMap);
        // 购买签到礼包
        $item->payEnvelope = $this->HomeRepository->countPayEnvelope($this->HomeRepository->getIds(), $timeMap);
        // 领取签到礼包
        $item->receiveEnvelope = $this->HomeRepository->sumReceiveEnvelope($this->HomeRepository->getIds(), $timeMap);
        // 订单数
        $item->bettingNumber = $this->HomeRepository->countBettingNumber($this->HomeRepository->getIds(), $timeMap);
        // 下单金额
        $item->bettingMoney = $this->HomeRepository->sumBettingMoney($this->HomeRepository->getIds(), $timeMap);
        // 服务费
        $item->serviceMoney = $this->HomeRepository->sumServiceMoney($this->HomeRepository->getIds(), $timeMap);
        // 购买签到礼包金额
        $item->payEnvelopeAmount = $this->HomeRepository->sumPayEnvelope($this->HomeRepository->getIds(), $timeMap);
        return $item;
    }
}
