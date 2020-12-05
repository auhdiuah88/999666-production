<?php


namespace App\Repositories\Admin;


use App\Models\Cx_System;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Redis;

class SystemRepository extends BaseRepository
{
    private $Cx_System;

    public function __construct(Cx_System $cx_System)
    {
        $this->Cx_System = $cx_System;
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
}
