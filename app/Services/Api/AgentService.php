<?php


namespace App\Services\Api;

use App\Dictionary\WithdrawalAmount;
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
        $limit = config('site.agent_withdraw',[]);
        if(!$limit){
            $limit = [
                'min' => WithdrawalAmount::MIN,
                'max' => WithdrawalAmount::MAX
            ];
        }
        $this->_data['min'] = $limit['min'];
        $this->_data['max'] = $limit['max'];
        $this->_data['total_commission'] = $this->AgentRepository->findCommission($id)->commission;//佣金总数
        if ($status == 2) {
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

    public function getRecommendRecharge($token, $type)
    {
        $id = $this->getUserId($token);
        ##获取总充值金额
        $this->_data = $this->AgentRepository->getRecommendRecharge($id, $type);
    }

}
