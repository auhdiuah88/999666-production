<?php


namespace App\Http\Controllers\Admin\agent;


use App\Http\Controllers\Controller;
use App\Services\Admin\agent\AgentUserService;

class AgentUserController extends Controller
{

    private $AgentUserService;

    public function __construct(AgentUserService $agentUserService)
    {
        $this->AgentUserService = $agentUserService;
    }

    public function index(){
        $this->AgentUserService->searchUser();
        return $this->AppReturn(
            $this->AgentUserService->_code,
            $this->AgentUserService->_msg,
            $this->AgentUserService->_data
        );
    }

}
