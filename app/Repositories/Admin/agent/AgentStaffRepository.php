<?php


namespace App\Repositories\Admin\agent;


use App\Models\Cx_User;

class AgentStaffRepository
{

    protected $Cx_User;

    public function __construct
    (
        Cx_User $cx_User
    )
    {
        $this->Cx_User = $cx_User;
    }

}
