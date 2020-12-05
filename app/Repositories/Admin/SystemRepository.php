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
        if(Redis::exists("SYSTEM_CONFIG")){
            $data=json_decode(Redis::get("SYSTEM_CONFIG"));
        }else{
            $data=$this->Cx_System->first();
            Redis::set("SYSTEM_CONFIG", json_encode($data,JSON_UNESCAPED_UNICODE));
        }
        return  true;
    }
}
