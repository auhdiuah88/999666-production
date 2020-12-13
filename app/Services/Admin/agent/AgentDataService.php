<?php


namespace App\Services\Admin\agent;


use App\Repositories\Admin\agent\AgentDataRepository;

class AgentDataService extends BaseAgentService
{

    protected $AgentDataRepository;

    public function __construct(AgentDataRepository $agentDataRepository){
        $this->AgentDataRepository = $agentDataRepository;
    }

    public function agentIndexData(){
        $this->getAdmin();
        $this->AgentDataRepository->user_id = $this->admin->user_id;

        $start_time = $this->strInput('start_time');
        $end_time = $this->strInput('end_time');
        if($start_time && $end_time){
            $time_map = [strtotime($start_time), strtotime($end_time)];
        }else{
            $time_map = [];
        }

        ##会员总数
        $member_total = $this->AgentDataRepository->getMemberTotal();

        ##新增会员
        $new_member_num = $this->AgentDataRepository->getNewMemberNum($start_time, $end_time);

        ##活跃人数
    }

}
