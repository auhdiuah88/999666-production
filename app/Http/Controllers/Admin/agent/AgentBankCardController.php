<?php


namespace App\Http\Controllers\Admin\agent;


use App\Http\Controllers\Controller;
use App\Services\Admin\agent\AgentBankCardService;

class AgentBankCardController extends Controller
{

    protected $AgentBackCardService;

    public function __construct(AgentBankCardService $agentBankCardService){
        $this->AgentBackCardService = $agentBankCardService;
    }

    public function backCardList(){
        try{
            $this->AgentBackCardService->backCardList();
            return $this->AppReturn(
                $this->AgentBackCardService->_code,
                $this->AgentBackCardService->_msg,
                $this->AgentBackCardService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

}
