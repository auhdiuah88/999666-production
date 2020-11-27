<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Sign_Order;
use App\Repositories\BaseRepository;

class SignRepository extends BaseRepository
{
    private $Cx_Sign_Order;

    public function __construct(Cx_Sign_Order $cx_Sign_Order)
    {
        $this->Cx_Sign_Order = $cx_Sign_Order;
    }

    public function findAll($offset, $limit)
    {
        return $this->Cx_Sign_Order->orderByDesc("start_time")->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countAll()
    {
        return $this->Cx_Sign_Order->count("id");
    }

    public function searchSignLogs($data, $offset, $limit)
    {
        return $this->whereCondition($data, $this->Cx_Sign_Order)->orderByDesc("start_time")->offset($offset)->limit($limit)->get()->toArray();
    }

    public function countSearchSignLogs($data)
    {
        return $this->whereCondition($data, $this->Cx_Sign_Order)->count("id");
    }
}
