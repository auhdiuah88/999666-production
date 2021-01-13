<?php


namespace App\Repositories\Api;


use App\Models\Cx_Product;
use Illuminate\Support\Facades\Redis;

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

    public function getProductById($product_id)
    {
        $product = Redis::hvals('GOODS_INFO:' . $product_id);
        if(!$product){
            $product = $this->Cx_Product->where("product_id", "=", $product_id)->with(
                [
                    'coverImg' => function($query){
                        $query->select(['image_id', 'path']);
                    },
                    'banner' => function($query){
                        $query->select(['image_id', 'path'])->orderBy('sort', 'asc');
                    }
                ]
            )->first();
            if($product)$product = $product->toArray();
//            if($product)
//                Redis::hset();
        }
        return $product;
    }

}
