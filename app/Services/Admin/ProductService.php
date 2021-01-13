<?php


namespace App\Services\Admin;


use App\Repositories\Admin\ProductRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

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

    public function add():bool
    {
        $data = $this->getProductData();
        $images = request()->post('images');
        if(empty($images)){
            $this->_code = 403;
            $this->_msg = '商品图片不能为空';
            return false;
        }
        DB::beginTransaction();
        try{
            ##增加商品
            $product = $this->ProductRepository->addProduct($data);
            if(!$product)throw new \Exception('商品创建失败');
            ##绑定banner
            $res = $this->ProductRepository->addProductImages($images, $product->product_id);
            if($res === false)throw new \Exception('banner关联失败');
            DB::commit();
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            $this->_code = 402;
            $this->_msg = $e->getMessage();
            return false;
        }
    }

    public function lists()
    {
        $size = $this->sizeInput();
        $this->_data = $this->ProductRepository->productLists($size);
    }

    public function update():bool
    {
        $product_id = $this->intInput('product_id');
        $data = $this->getProductData();
        $images = request()->post('images');
        if(empty($images)){
            $this->_code = 403;
            $this->_msg = '商品图片不能为空';
            return false;
        }
        DB::beginTransaction();
        try{
            ##增加商品
            $res = $this->ProductRepository->updateProduct($product_id, $data);
            if($res === false)throw new \Exception('商品更新失败');
            ##删除原来的banner关联
            $res = $this->ProductRepository->delProductImages($product_id);
            if($res === false)throw new \Exception('banner更新失败');
            ##绑定banner
            $res = $this->ProductRepository->addProductImages($images, $product_id);
            if($res === false)throw new \Exception('banner关联失败');
            DB::commit();
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            $this->_code = 402;
            $this->_msg = $e->getMessage();
            return false;
        }
    }

    protected function getProductData():array
    {
        return [
            'name' => $this->strInput('name'),
            'price' => $this->floatInput('price'),
            'back_money' => $this->floatInput('back_money'),
            'content' => $this->htmlInput('content'),
            'sort' => $this->intInput('sort',9999),
            'status' => $this->intInput('status'),
            'cover' => $this->intInput('cover')
        ];
    }

    public function edit():bool
    {
        $product_id = $this->intInput('product_id');
        $field = $this->strInput('field');
        switch($field){
            case 'status':
                $status = $this->ProductRepository->getProductValue($product_id,'status');
                $value = ($status + 1)%2;
                break;
            case 'sort':
                $value = $this->intInput('sort',9999);
                break;
            default:
                break;
        }
        if(!isset($value)){
            $this->_code = 403;
            $this->_msg = '参数错误';
            return false;
        }
        $res = $this->ProductRepository->updateProduct($product_id, [$field=>$value]);
        if($res === false){
            $this->_code = 402;
            $this->_msg = '修改失败';
            return false;
        }
        return true;
    }

    public function detail():bool
    {
        $product_id = $this->intInput('product_id');
        $product = $this->ProductRepository->getProduct($product_id);
        if(!$product){
            $this->_code = 402;
            $this->_msg = '商品不存在';
            return false;
        }
        $this->_data = $product;
        return true;
    }

    public function del()
    {
        $product_id = $this->intInput('product_id');
        $this->ProductRepository->delProduct($product_id);
    }

}
