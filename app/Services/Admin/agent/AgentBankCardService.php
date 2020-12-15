<?php


namespace App\Services\Admin\agent;


use App\Repositories\Admin\agent\AgentUserRepository;

class AgentBankCardService extends BaseAgentService
{

    protected $AgentUserRepository;

    public function __construct(AgentUserRepository $agentUserRepository)
    {
        $this->AgentUserRepository = $agentUserRepository;
    }

    public function backCardList(){
        $rule = [
            'phone'
        ];
    }

}
