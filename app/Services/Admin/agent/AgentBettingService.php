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
        $list = $this->agentBettingRepository->orders($admin_id);
        $total = $this->agentBettingRepository->ordersCount();
        $this->_data = ["total" => $total, "list" => $list];
    }
}
