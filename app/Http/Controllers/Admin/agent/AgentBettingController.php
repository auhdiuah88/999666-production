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

    public function orders(Request $request)
    {
        $this->AgentBettingService->orders($request->get("page"), $request->get("limit"));
        return $this->AppReturn(
            $this->AgentBettingService->_code,
            $this->AgentBettingService->_msg,
            $this->AgentBettingService->_data
        );
    }
}
