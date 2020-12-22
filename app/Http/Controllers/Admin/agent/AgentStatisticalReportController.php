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

    /**
     * 当日会员盈利榜
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
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

    /**
     * 当日会员亏损帮
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function dailyLoseRank(){
        try{
            $this->StatisticalService->dailyLoseRank();
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

}
