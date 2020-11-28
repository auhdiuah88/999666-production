<?php


namespace App\Repositories\Api;


use App\Models\Cx_User;
use App\Repositories\BaseRepository;

class AgentRepository extends BaseRepository
{
    private $Cx_User;

    public function __construct(Cx_User $cx_User)
    {
        $this->Cx_User = $cx_User;
    }

    public function findOne($id)
    {
        return $this->Cx_User->where("id", $id)->select(["one_commission"])->first();
    }

    public function countOne($id)
    {
        return $this->Cx_User->where("one_recommend_id", $id)->count();
    }

    public function findTwo($id)
    {
        return $this->Cx_User->where("id", $id)->select(["two_commission"])->first();
    }

    public function countTwo($id)
    {
        return $this->Cx_User->where("two_recommend_id", $id)->count();
    }

    public function getExtensionUser($id, $offset, $limit)
    {
        return $this->Cx_User->where("one_recommend_id", $id)->whereOr("two_recommend_id", $id)->select(["id", "phone", "nickname"])->offset($offset)->limit($limit)->orderByDesc("reg_time")->get()->toArray();
    }

    public function countExtensionUser($id)
    {
        return $this->Cx_User->where("one_recommend_id", $id)->whereOr("two_recommend_id", $id)->count("id");
    }
}
