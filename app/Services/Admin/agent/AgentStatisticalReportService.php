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
        $this->setDailyWinRankWhere();
        $data = $this->AgentStatisticalReportRepository->dailyWinRank($this->where, $size, $page);
        $this->_data = $data;
        return true;
    }

    protected function setDailyWinRankWhere(){
        $where = " where betting_time BETWEEN " . day_start() ." AND " . day_end() . " ";
        $where .= " AND type = 1 ";
        $this->user_ids = $this->AgentUserRepository->getUserIds($this->getRelationWhere($this->admin->user_id));
        if($this->user_ids){
            $where .= " AND user_id in ( " . implode(',',$this->user_ids) .")";
        }else{
            $where .= " AND user_id in ( " . 0 .")";
        }
        $this->where = $where;
    }

}
