<?php


namespace App\Services\Admin\agent;


use App\Repositories\Admin\agent\AgentStatisticalReportRepository;
use App\Repositories\Admin\agent\AgentUserRepository;

class AgentStatisticalReportService extends BaseAgentService
{

    protected $AgentUserRepository, $AgentStatisticalReportRepository;

    protected $where, $user_ids;

    public function __construct(AgentUserRepository $agentUserRepository, AgentStatisticalReportRepository $agentStatisticalReportRepository){
        $this->AgentUserRepository = $agentUserRepository;
        $this->AgentStatisticalReportRepository = $agentStatisticalReportRepository;
    }

    public function dailyWinRank(){
        $this->getAdmin();
        $size = $this->sizeInput();
        $page = $this->pageInput();
        $this->setDailyRankWhere();
        $data = $this->AgentStatisticalReportRepository->dailyWinRank($this->where, $size, $page);
        $this->_data = $data;
        return true;
    }

    public function dailyLoseRank(){
        $this->getAdmin();
        $size = $this->sizeInput();
        $page = $this->pageInput();
        $this->setDailyRankWhere();
        $data = $this->AgentStatisticalReportRepository->dailyLoseRank($this->where, $size, $page);
        $this->_data = $data;
        return true;
    }

    protected function setDailyRankWhere(){
        $where = " where gb.betting_time BETWEEN " . day_start() ." AND " . day_end() . " ";
        $where .= " AND gb.type = 1 ";
        $this->user_ids = $this->AgentUserRepository->getUserIds($this->getRelationWhere($this->admin->user_id));
        if($this->user_ids){
            $where .= " AND gb.user_id in ( " . implode(',',$this->user_ids) .")";
        }else{
            $where .= " AND gb.user_id in ( " . 0 .")";
        }
        $this->where = $where;
    }

}
