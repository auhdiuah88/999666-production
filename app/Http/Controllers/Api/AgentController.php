<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Services\Api\AgentService;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    private $AgentService;

    public function __construct(AgentService $agentService)
    {
        $this->AgentService = $agentService;
    }

    public function getAgentInformation(Request $request)
    {
        $this->AgentService->getAgentInformation($request->header("token"), $request->post("status"));
        return $this->AppReturn(
            $this->AgentService->_code,
            $this->AgentService->_msg,
            $this->AgentService->_data
        );
    }

    public function getExtensionUser(Request $request)
    {
        $this->AgentService->getExtensionUser($request->header("token"), $request->post("page"), $request->post("limit"));
        return $this->AppReturn(
            $this->AgentService->_code,
            $this->AgentService->_msg,
            $this->AgentService->_data
        );
    }
}
