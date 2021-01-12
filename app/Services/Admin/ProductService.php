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
        $data = [
            'name' => $this->strInput('name'),
            'price' => $this->floatInput('price'),
            'back_money' => $this->floatInput('back_money'),
            'content' => $this->htmlInput('content'),
            'sort' => $this->intInput('sort',9999),
            'status' => $this->intInput('status'),
            'cover' => $this->intInput('cover')
        ];
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

}
