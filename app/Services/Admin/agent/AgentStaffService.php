<?php


namespace App\Services\Admin\agent;


use App\Repositories\Admin\agent\AgentStaffRepository;
use App\Repositories\Admin\agent\AgentUserRepository;

class AgentStaffService extends BaseAgentService
{

    protected $where;

    protected $AgentStaffRepository, $AgentUserRepository;

    public function __construct(AgentStaffRepository $agentStaffRepository, AgentUserRepository $agentUserRepository)
    {
        $this->AgentStaffRepository = $agentStaffRepository;
        $this->AgentUserRepository = $agentUserRepository;
    }

    public function getLists()
    {
        ##查询管理下的员工的总拉新,总充值,总投注,总输赢,今日拉新,今日充值,今日投注,今日输赢
        $this->getAdmin();
        $this->setGetListsWhere();
        $data = $this->AgentStaffRepository->getLists($this->where, $this->sizeInput(), $this->pageInput());
        $this->_data = $data;
        return true;
    }

    protected function setGetListsWhere()
    {
        $is_group_leader = $this->intInput('is_group_leader');
        $where['is_customer_service'] = ['=', 1];
        if($is_group_leader){
            $where['invite_relation'] = ['like', "%-{$this->admin->user_id}-%"];
            $where['is_group_leader'] = ['=', 2];
        }else{
            $where['is_group_leader'] = ['=', 1];
        }
        $this->where = $where;
    }

}
