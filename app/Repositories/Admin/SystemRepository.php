<?php


namespace App\Repositories\Admin;


use App\Models\Cx_System;
use App\Models\Cx_System_Tips;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Redis;

class SystemRepository extends BaseRepository
{
    private $Cx_System, $Cx_System_Tips;

    public function __construct
    (
        Cx_System $cx_System,
        Cx_System_Tips $system_Tips
    )
    {
        $this->Cx_System = $cx_System;
        $this->Cx_System_Tips = $system_Tips;
    }

    public function findAll()
    {
        return $this->Cx_System->first();
    }

    public function editSystem($data)
    {
        $this->Cx_System->where("id", $data["id"])->update($data);
        $row=$this->Cx_System->first();
        Redis::set("SYSTEM_CONFIG", json_encode($row,JSON_UNESCAPED_UNICODE));
        return  true;
    }

    public function tipsList($where, $size)
    {
        return makeModel($where, $this->Cx_System_Tips)
            ->select("id", "status", "content", "update_time", "create_time", "start_time", "end_time")
            ->paginate($size);
    }

    public function addTips($data)
    {
        return $this->Cx_System_Tips->create($data);
    }

    public function editTips($id, $data)
    {
        return $this->Cx_System_Tips->where("id", $id)->update($data);
    }

    public function delTips($id)
    {
        return $this->Cx_System_Tips->where("id", $id)->delete();
    }
}
