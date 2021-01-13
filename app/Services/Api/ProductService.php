<?php


namespace App\Services\Api;


use App\Repositories\Api\ProductRepository;
use \App\Services\BaseService;

class ProductService extends BaseService
{

    protected $ProductRepository;

    public function __construct
    (
        ProductRepository $productRepository
    )
    {
        $this->ProductRepository = $productRepository;
    }

    public function lists():bool
    {
        $size = request()->input('size',10);
        $data = $this->ProductRepository->getProductList($size);
        if(!$data){
            $this->_code = 401;
            $this->_msg = 'No more commodity data';
            return false;
        }
        $this->_data = $data;
        return true;
    }

    public function detail():bool
    {
        $product_id = request()->input('product_id',0);
        $product = $this->ProductRepository->getProductById($product_id);
        if(!$product){
            $this->_code = 401;
            $this->_msg = '商品不存在';
            return false;
        }
        if($product['status'] != 1){
            $this->_code = 401;
            $this->_msg = '商品已下架';
            return false;
        }
        $this->_data = $product;
        return true;
    }

}
