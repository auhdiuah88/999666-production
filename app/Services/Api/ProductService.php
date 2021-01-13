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

}
