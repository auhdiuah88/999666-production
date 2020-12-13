<?php


namespace App\Http\Controllers\Admin\agent;


use App\Http\Controllers\Controller;
use App\Services\Admin\agent\AgentDataService;

class AgentDataController extends Controller
{

    protected $AgentDataService;

    public function __construct(AgentDataService $agentDataService){
        $this->AgentDataService = $agentDataService;
    }

    public function index(){
        try{
            $this->AgentDataService->agentIndexData();
            return $this->AppReturn(
                $this->AgentDataService->_code,
                $this->AgentDataService->_msg,
                $this->AgentDataService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

}
