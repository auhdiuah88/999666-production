<?php


namespace App\Services\Admin;


use App\Repositories\Admin\ReportRepository;
use App\Services\BaseService;

class ReportService extends BaseService
{
    private $ReportRepository;

    public function __construct(ReportRepository $reportRepository)
    {
        $this->ReportRepository = $reportRepository;
    }

    public function findAll($page, $limit)
    {
        $timeMap = [strtotime(date("Y-m-d 00:00:00")), strtotime(date("Y-m-d 23:59:59"))];
        $list = $this->getContext($page, $limit, $timeMap);
        $total = $this->ReportRepository->countAll();
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function searchReport($data)
    {
        $list = $this->getContext($data["page"], $data["limit"], $data["timeMap"]);
        $total = $this->ReportRepository->countAll();
        $this->_data = ["total" => $total, "list" => $list];
    }

    public function getContext($page, $limit, $timeMap)
    {
        $customers = $this->ReportRepository->findAll(($page - 1) * $limit, $limit);
        $customers->map(function ($item) use ($timeMap) {
            // 会员数
            $item->members = $this->ReportRepository->countMembers($item->id);
            // 新增会员
            $item->newMembers = $this->ReportRepository->countNewMembers($item->id, $timeMap);
            // 普通新增会员
            $item->ordinaryMembers = $this->ReportRepository->countOrdinaryMembers($item->id, $timeMap);
            // 代理裂变会员
            $item->agentMembers = $this->ReportRepository->countAgentMembers($item->id, $timeMap);
            // 红包裂变会员
            $item->envelopeMembers = $this->ReportRepository->countEnvelopeMembers($item->id, $timeMap);
            // 活跃人数
            $item->activePeopleNumber = $this->ReportRepository->countActivePeopleNumber($item->id, $timeMap);
            // 首充人数
            $item->firstChargeNumber = $this->ReportRepository->countFirstChargeNumber($item->id, $timeMap);
            // 充值金额
            $item->rechargeMoney = $this->ReportRepository->sumRechargeMoney($this->ReportRepository->getIds($item->id), $timeMap);
            // 提现金额
            $item->withdrawalMoney = $this->ReportRepository->sumWithdrawalMoney($this->ReportRepository->getIds($item->id), $timeMap);
            // 待提现金额
            $item->toBeWithdrawalMoney = $this->ReportRepository->sumUserBalance($item->id) + $this->ReportRepository->sumUserCommission($item->id);
            // 订单分佣
            $item->subCommission = $this->ReportRepository->sumSubCommission($this->ReportRepository->getIds($item->id), $timeMap);
            // 赠金
            $item->giveMoney = $this->ReportRepository->sumGiveMoney($this->ReportRepository->getIds($item->id), $timeMap);
            // 购买签到礼包
            $item->payEnvelope = $this->ReportRepository->countPayEnvelope($this->ReportRepository->getIds($item->id), $timeMap);
            // 领取签到礼包
            $item->receiveEnvelope = $this->ReportRepository->sumReceiveEnvelope($this->ReportRepository->getIds($item->id), $timeMap);
            // 订单数
            $item->bettingNumber = $this->ReportRepository->countBettingNumber($this->ReportRepository->getIds($item->id), $timeMap);
            // 下单金额
            $item->bettingMoney = $this->ReportRepository->sumBettingMoney($this->ReportRepository->getIds($item->id), $timeMap);
            // 服务费
            $item->serviceMoney = $this->ReportRepository->sumServiceMoney($this->ReportRepository->getIds($item->id), $timeMap);

            return $item;
        });
        return $customers;
    }
}
