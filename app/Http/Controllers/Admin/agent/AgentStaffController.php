<?php


namespace App\Http\Controllers\Admin\agent;


use App\Http\Controllers\Controller;
use App\Services\Admin\agent\AgentStaffService;

class AgentStaffController extends Controller
{

    private $AgentStaffService;

    public function __construct
    (
        AgentStaffService $agentStaffService
    )
    {
        $this->AgentStaffService = $agentStaffService;
    }

    public function staffLists()
    {
        try{
            $this->AgentStaffService->getLists();
            return $this->AppReturn(
                $this->AgentStaffService->_code,
                $this->AgentStaffService->_msg,
                $this->AgentStaffService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(403, $e->getMessage());
        }
    }

}
