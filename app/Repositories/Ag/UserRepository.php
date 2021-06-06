<?php


namespace App\Repositories\Ag;


use App\Models\Cx_Ag_Link;
use App\Models\Cx_User;
use Illuminate\Support\Facades\Cache;

class UserRepository
{

    protected $Cx_Users, $Cx_Ag_Link;

    public function __construct
    (
        Cx_User $cx_User,
        Cx_Ag_Link $cx_Ag_Link
    )
    {
        $this->Cx_Users = $cx_User;
        $this->Cx_Ag_Link = $cx_Ag_Link;
    }

    public function getById($id)
    {
        return $this->Cx_Users->where("id", $id)->first();
    }

    public function addLink($data)
    {
        return $this->Cx_Ag_Link->create($data);
    }

    public function getLinkList()
    {
        $user_id = Cache::get('user')['id'];
        return $this->Cx_Ag_Link->where("user_id", $user_id)->select("id", "link", "type", "rebate_percent", "created_at")->paginate(10);
    }

    public function delLink($id)
    {
        return $this->Cx_Ag_Link->where("id", $id)->update(['deleted_at'=>date("Y-m-d H:i:s")]);
    }

}
