<?php


namespace App\Services\Api;


use App\Repositories\Api\ProductRepository;
use App\Repositories\Api\UserRepository;
use \App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class ProductService extends BaseService
{

    protected $ProductRepository, $UserRepository;

    public function __construct
    (
        ProductRepository $productRepository,
        UserRepository $userRepository
    )
    {
        $this->ProductRepository = $productRepository;
        $this->UserRepository = $userRepository;
    }

    public function lists():bool
    {
        $size = request()->input('size',10);
        $data = $this->ProductRepository->getProductList($size);
        if($data->isEmpty()){
            $this->_code = 402;
            $this->_msg = 'No more commodity data';
            $this->_data = [];
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
            $this->_code = 402;
            $this->_msg = 'The product does not exist';
            return false;
        }
        if($product['status'] != 1){
            $this->_code = 402;
            $this->_msg = 'The goods have been taken off the shelves';
            return false;
        }
        $this->_data = $product;
        return true;
    }

    public function buy():bool
    {
        $product_id = request()->input('product_id',0);
        $product = $this->ProductRepository->getProductById($product_id);
        if(!$product){
            $this->_code = 402;
            $this->_msg = 'The product does not exist';
            return false;
        }
        if($product['status'] != 1){
            $this->_code = 402;
            $this->_msg = 'The goods have been taken off the shelves';
            return false;
        }
        if($product['buy_status'] != 1){
            $this->_code = 402;
            $this->_msg = 'Commodity is not tradable';
            return false;
        }
        $num = request()->post('num',1);
        $user_id = request()->get('userInfo')['id'];
        $userInfo = $this->UserRepository->findByIdUser($user_id);
        $total_price = bcmul($num, $product['price'],2);
        if($userInfo->point < $total_price){
            $this->_code = 402;
            $this->_msg = 'Insufficient balance is not enough';
            return false;
        }
        $total_back_money = bcmul($num, $product['back_money'],2);
        $order_data = [
            'product_id' => $product_id,
            'num' => $num,
            'user_id' => $user_id,
            'price' => $total_price,
            'back_money' => $total_back_money,
            'product_data' => $product
        ];
        DB::beginTransaction();
        try{
            ##???????????????????????????
            $log_id = $this->UserRepository->buyProduct($userInfo, $total_price, $total_back_money);
            if(!$log_id)throw new \Exception('The purchase failed');
            ##????????????
            $order_data['balance_log_id'] = $log_id;
            $res = $this->ProductRepository->createOrder($order_data);
            if(!$res)throw new \Exception('Order creation failed');
            ##????????????
            $this->ProductRepository->addSaleNum($product_id, $num);
            DB::commit();
            $this->_msg = 'The lottery money has arrived';
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            $this->_msg = $e->getMessage();
            $this->_code = 402;
            return false;
        }
    }

    public function orders():bool
    {
        $size = request()->input('size',10);
        $user_id = request()->get('userInfo')['id'];
        $where = [
            'user_id' => ['=', $user_id]
        ];
        $data = $this->ProductRepository->orders($where, $size);
        if($data->isEmpty()){
            $this->_code = 402;
            $this->_msg = 'No more order data';
            return false;
        }
        $this->_data = $data;
        return true;
    }

}
