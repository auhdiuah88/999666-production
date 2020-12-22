<?php


namespace App\Http\Controllers\Admin\agent;


use App\Http\Controllers\Controller;
use App\Services\Admin\agent\AgentStatisticalReportService;

class AgentStatisticalReportController extends Controller
{

    private $StatisticalService;

    public function __construct(AgentStatisticalReportService $agentStatisticalReportService)
    {
        $this->StatisticalService = $agentStatisticalReportService;
    }

    public function dailyWinRank(){
        try{
            $this->StatisticalService->dailyWinRank();
            return $this->AppReturn(
                $this->StatisticalService->_code,
                $this->StatisticalService->_msg,
                $this->StatisticalService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function dailyLoseRank(){

    }

}
