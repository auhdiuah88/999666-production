<?php

namespace App\Services\Admin\agent;


use App\Repositories\Admin\agent\AgentBettingRepository;
use App\Services\BaseService;

class AgentBettingService extends BaseService
{

    private $agentBettingRepository;

    public function __construct(AgentBettingRepository $agentBettingRepository)
    {
        $this->agentBettingRepository = $agentBettingRepository;
    }

    public function orders($page, $limit)
    {
        $admin_id = request()->get('admin_id');
        $list = $this->agentBettingRepository->orders($admin_id, max($page - 1, 0) * $limit, $limit);
        $total = $this->agentBettingRepository->ordersCount($admin_id);
        $this->_data = ["total" => $total, "list" => $list];
    }

    //统计
    public function statistic()
    {
        $admin_id = request()->get('admin_id');
        $this->_data = $this->agentBettingRepository->statistic($admin_id);
    }
}
