<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Product;
use App\Models\Cx_Product_Images;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Redis;

class ProductRepository extends BaseRepository
{

    protected $Cx_Product, $Cx_Product_Images;

    public function __construct
    (
        Cx_Product $cx_Product,
        Cx_Product_Images $cx_Product_Images
    )
    {
        $this->Cx_Product = $cx_Product;
        $this->Cx_Product_Images = $cx_Product_Images;
    }

    public function addProduct($data)
    {
        return $this->Cx_Product->create($data);
    }

    public function updateProduct($product_id, $data)
    {
        return $this->Cx_Product->where("product_id", "=", $product_id)->update($data);
    }

    public function addProductImages($images, $product_id)
    {
        $data = [];
        foreach($images as $image){
            $data[] = [
                'product_id' => $product_id,
                'file_id' => $image['file_id'],
                'sort' => $image['sort']
            ];
        }
        return $this->Cx_Product_Images->insert($data);
    }

    public function delProductImages($product_id)
    {
        return $this->Cx_Product_Images->where("product_id", "=", $product_id)->delete();
    }

    public function productLists($size)
    {
        return $this->Cx_Product
            ->with(
                [
                    'coverImg' => function($query){
                        $query->select(['image_id', 'path']);
                    },
                    'banner' => function($query){
                        $query->select(['image_id', 'path'])->orderBy('sort', 'asc');
                    }
                ]
            )
            ->paginate($size);
    }

    public function getProductValue($product_id, $field)
    {
        return $this->Cx_Product->where("product_id", "=", $product_id)->value($field);
    }

    public function getProduct($product_id)
    {
        return $this->Cx_Product
            ->where("product_id", "=", $product_id)
            ->with(
                [
                    'coverImg' => function($query){
                        $query->select(['image_id', 'path']);
                    },
                    'banner' => function($query){
                        $query->select(['image_id', 'path'])->orderBy('sort', 'asc');
                    }
                ]
            )
            ->first();
    }

    public function delProduct($product_id)
    {
        return $this->Cx_Product->where("product_id", "=", $product_id)->delete();
    }

    public function updateProductCache($product_id)
    {
        $product = $this->Cx_Product->where("product_id", "=", $product_id)->with(
            [
                'coverImg' => function($query){
                    $query->select(['image_id', 'path']);
                },
                'banner' => function($query){
                    $query->select(['image_id', 'path'])->orderBy('sort', 'asc');
                }
            ]
        )->first()->toArray();
        redisHSetAll('GOODS_INFO:' . $product_id, $product);
    }

    public function delProductCache($product_id)
    {
        Redis::del('GOODS_INFO:' . $product_id);
    }

}
