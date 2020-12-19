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

    public function findAll($page, $limit)
    {
        $list = $this->agentBettingRepository->findAll(($page - 1) * $limit, $limit);
        $total = $this->agentBettingRepository->count();
        $this->_data = ["total" => $total, "list" => $list];
    }
}
