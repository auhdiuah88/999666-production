<?php


namespace App\Repositories\Api;


use App\Models\Cx_Product;

class ProductRepository
{

    protected $Cx_Product;

    public function __construct
    (
        Cx_Product $cx_Product
    )
    {
        $this->Cx_Product = $cx_Product;
    }

    public function getProductList($size)
    {
        return $this->Cx_Product
            ->where("status", "=", 1)
            ->orderBy("sort", 'asc')
            ->with(
                [
                    'coverImg' => function($query){
                        $query->select(['image_id', 'path']);
                    }
                ]
            )
            ->select(['product_id', 'name', 'price', 'back_money', 'cover'])
            ->paginate($size);
    }

}
