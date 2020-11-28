<?php


namespace App\Services\Api;

use App\Repositories\Api\AgentRepository;
use App\Services\BaseService;

class AgentService extends BaseService
{
    private $AgentRepository;

    public function __construct(AgentRepository $agentRepository)
    {
        $this->AgentRepository = $agentRepository;
    }

    public function getAgentInformation($token, $status)
    {
        $id = $this->getUserId($token);
        if ($status == 1) {
            $this->_data["commission"] = $this->AgentRepository->findOne($id)->one_commission;
            $this->_data["number"] = $this->AgentRepository->countOne($id);
            return;
        }
        $this->_data["commission"] = $this->AgentRepository->findTwo($id)->two_commission;
        $this->_data["number"] = $this->AgentRepository->countTwo($id);
    }

    public function getExtensionUser($token, $page, $limit)
    {
        $id = $this->getUserId($token);
        $list = $this->AgentRepository->getExtensionUser($id, ($page - 1) * $limit, $limit);
        $total = $this->AgentRepository->countExtensionUser($id);
        $this->_data = ["total" => $total, "list" => $list];
    }
}
