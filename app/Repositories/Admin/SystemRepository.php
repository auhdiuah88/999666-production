<?php


namespace App\Repositories\Admin;


use App\Models\Cx_System;
use App\Repositories\BaseRepository;

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
        return $this->Cx_System->where("id", $data["id"])->update($data);
    }
}
