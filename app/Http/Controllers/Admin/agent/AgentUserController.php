<?php


namespace App\Http\Controllers\Admin\agent;


use App\Http\Controllers\Controller;
use App\Services\Admin\agent\AgentUserService;
use Illuminate\Support\Facades\Log;

class AgentUserController extends Controller
{

    private $AgentUserService;

    public function __construct(AgentUserService $agentUserService)
    {
        $this->AgentUserService = $agentUserService;
    }

    public function index(){
        try{
            $this->AgentUserService->searchUser();
            return $this->AppReturn(
                $this->AgentUserService->_code,
                $this->AgentUserService->_msg,
                $this->AgentUserService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function firstRechargeList(){
        try{
            $this->AgentUserService->firstRechargeList();
            return $this->AppReturn(
                $this->AgentUserService->_code,
                $this->AgentUserService->_msg,
                $this->AgentUserService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

}
