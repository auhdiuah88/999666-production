<?php


namespace App\Repositories\Admin;


use App\Models\Cx_White_List;
use App\Repositories\BaseRepository;

class WhiteListRepository extends BaseRepository
{
    private $Cx_White_List;

    public function __construct(Cx_White_List $cx_White_List)
    {
        $this->Cx_White_List = $cx_White_List;
    }

    public function findAll($offset, $limit)
    {
        return $this->Cx_White_List->orderByDesc("create_time")->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countAll()
    {
        return $this->Cx_White_List->count("id");
    }

    public function findById($id)
    {
        return $this->Cx_White_List->where("id", $id)->first();
    }

    public function addIp($data)
    {
        return $this->Cx_White_List->insertGetId($data);
    }

    public function editIp($data)
    {
        return $this->Cx_White_List->where("id", $data["id"])->update($data);
    }

    public function delIp($id)
    {
        return $this->Cx_White_List->where("id", $id)->delete();
    }

    public function ips()
    {
        return $this->Cx_White_List->pluck('ip');
    }
}
