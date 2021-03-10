<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Sign_Product;
use App\Repositories\BaseRepository;

class ActivityRepository extends BaseRepository
{
    private $Cx_Sign_Products;

    public function __construct
    (
        Cx_Sign_Product $cx_Sign_Product
    )
    {
        $this->Cx_Sign_Products = $cx_Sign_Product;
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

}
