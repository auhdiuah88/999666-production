<?php


namespace App\Services\Admin\agent;


use App\Repositories\Admin\agent\AgentBankCardRepository;
use App\Repositories\Admin\agent\AgentUserRepository;
use Illuminate\Support\Facades\DB;

class AgentBankCardService extends BaseAgentService
{

    protected $where;

    protected $AgentUserRepository, $AgentBankCardRepository;

    public function __construct(AgentUserRepository $agentUserRepository, AgentBankCardRepository $agentBankCardRepository)
    {
        $this->AgentUserRepository = $agentUserRepository;
        $this->AgentBankCardRepository = $agentBankCardRepository;
    }

    public function backCardList(){
        $this->getAdmin();
        $size = $this->sizeInput();
        $this->setBankCardListWhere();
        $account_holder = $this->strInput("account_holder");
        $this->_data = $this->AgentBankCardRepository->getBackCardList($this->where,$account_holder, $size);
        return true;
    }

    protected function setBankCardListWhere(){
        $where['invite_relation'] = ['like', '%-'. $this->admin->user_id .'-%'];
        $user_id = $this->intInput('user_id');
        if($user_id > 0)
            $where["user_id"] = ["=", $user_id];
        $phone = $this->strInput("phone");
        if($phone)
            $where["phone"] = ["=", $phone];
        $user_ids = $this->AgentUserRepository->getUserIds($where);
        $this->where = $user_ids;
    }

}
