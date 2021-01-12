<?php


namespace App\Repositories\Admin;


use App\Models\Cx_Product;
use App\Models\Cx_Product_Images;
use App\Repositories\BaseRepository;

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

}
