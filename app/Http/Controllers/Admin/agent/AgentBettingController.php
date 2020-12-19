<?php


namespace App\Http\Controllers\Admin\agent;


use App\Http\Controllers\Controller;
use App\Services\Admin\agent\AgentBettingService;
use Illuminate\Http\Request;

class AgentBettingController extends Controller
{
    private $AgentBettingService;

    public function __construct(AgentBettingService $agentBettingService)
    {
        $this->AgentBettingService = $agentBettingService;
    }

    public function findAll(Request $request)
    {
        $this->AgentBettingService->findAll($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->BettingService->_code,
            $this->BettingService->_msg,
            $this->BettingService->_data
        );
    }
}
