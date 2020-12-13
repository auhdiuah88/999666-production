<?php


namespace App\Repositories\Admin\agent;


use App\Models\Cx_User;

class AgentDataRepository
{

    protected $Cx_user;

    public $user_id;

    public function __construct(Cx_User $cx_User){
        $this->Cx_user = $cx_User;
    }

    public function getMemberTotal(){
        return $this->Cx_user->where('invite_relation', 'like', '%-'. $this->user_id .'-%')->count();
    }

    public function getNewMemberNum(){
        $where = [
            ['invite_relation', 'like', '%-'. $this->user_id .'-%']
        ];
        if($start_time && $end_time)
            $where[] = ['reg_time', 'BETWEEN', [$start_time, $end_time]];
        return $this->Cx_user->where($where)->count();
    }

    public function getActiveMemberNum(){

    }

}
