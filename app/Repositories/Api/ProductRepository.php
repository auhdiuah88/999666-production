<?php


namespace App\Repositories\Api;


use App\Models\Cx_Product;
use App\Models\Cx_Product_Orders;

class ProductRepository
{

    protected $Cx_Product, $Cx_Product_Orders;

    public function __construct
    (
        Cx_Product $cx_Product,
        Cx_Product_Orders $cx_Product_Orders
    )
    {
        $this->Cx_Product = $cx_Product;
        $this->Cx_Product_Orders = $cx_Product_Orders;
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
        $product = redisHGetALl('GOODS_INFO:' . $product_id, ['banner', 'cover_img']);
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
            if($product){
                $product = $product->toArray();
                redisHSetAll('GOODS_INFO:' . $product_id, $product);
            }
        }
        return $product;
    }

    public function createOrder($data)
    {
        return $this->Cx_Product_Orders->create($data);
    }

    public function addSaleNum($product_id, $num)
    {
        return $this->Cx_Product->where("product_id", "=", $product_id)->increment("sale_num", $num);
    }

}
