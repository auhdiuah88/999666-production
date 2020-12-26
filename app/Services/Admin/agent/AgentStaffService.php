<?php


namespace App\Services\Admin\agent;


use App\Repositories\Admin\agent\AgentUserRepository;

class AgentStaffService extends BaseAgentService
{

    protected $where;

    protected $AgentStaffService;

    public function __construct(AgentStaffService $agentStaffService)
    {
        $this->AgentStaffService = $agentStaffService;
    }

    public function getLists()
    {
        ##查询管理下的员工的总拉新,总充值,总投注,总输赢,今日
    }

}
