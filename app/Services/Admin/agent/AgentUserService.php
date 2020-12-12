<?php


namespace App\Services\Admin\agent;


use App\Repositories\Admin\agent\AgentUserRepository;
use App\Services\BaseService;

class AgentUserService extends BaseService
{

    private $AgentUserRepository;

    public function __construct(AgentUserRepository $agentUserRepository){
        $this->AgentUserRepository = $agentUserRepository;
    }

    public function searchUser(){
//        $user =
    }

}
