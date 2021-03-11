<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Sign_Product;
use App\Models\Cx_Task;
use App\Repositories\BaseRepository;

class ActivityRepository extends BaseRepository
{
    private $Cx_Sign_Products, $Cx_Task;

    public function __construct
    (
        Cx_Sign_Product $cx_Sign_Product,
        Cx_Task $cx_Task
    )
    {
        $this->Cx_Sign_Products = $cx_Sign_Product;
        $this->Cx_Task = $cx_Task;
    }

    public function getSignProduct()
    {
        return $this->Cx_Sign_Products
            ->orderBy('amount','asc')
            ->select(['id', 'name', 'status', 'amount', 'daily_rebate', 'receive_amount', 'payback_cycle', 'rebate_ratio', 'stock', 'profit'])
            ->get();
    }

    public function signProductEdit($data)
    {
        return $this->Cx_Sign_Products->where('id', '=', $data['id'])->update($data);
    }

    public function getRedEnvelopeTask()
    {
        $list =  $this->Cx_Task
            ->select(['id', 'name', 'status', 'value', 'reward', 'expire'])
            ->orderBy('value', 'asc')
            ->get();
        foreach($list as &$item){
            $item->expire = date('Y-m-d H:i:s', $item->expire);
        }
        return $list;
    }

    public function redEnvelopeTaskEdit($data)
    {
        return $this->Cx_Task->where('id', '=', $data['id'])->update($data);
    }

}
