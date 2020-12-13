<?php


namespace App\Services\Admin\agent;


use App\Repositories\Admin\agent\AgentUserRepository;
use App\Services\BaseService;

class BaseAgentService extends BaseService
{

    protected $admin_id;

    protected $admin;

    protected $AgentUserRepository;

    public function searchInput($key){
        return search_filter(request()->input($key,''));
    }

    public function strInput($key){
        return str_filter(request()->input($key,''));
    }

    public function intInput($key, $default=0){
        return intval(request()->input($key, $default));
    }

    public function relationLike(){
        return ['invite_relation', 'like', '%-'. $this->admin->user_id .'-%'];
    }

    public function sizeInput($default=10){
        return min(intval(request()->input('size',$default)),30);
    }

    protected function getAdmin(){
        $this->admin_id = request()->get('admin_id');
        $this->admin = $this->AgentUserRepository->getAdminUserId($this->admin_id);
    }

}
