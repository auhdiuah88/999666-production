<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\ProductService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{

    protected $ProductService;

    public function __construct
    (
        ProductService $productService
    )
    {
        $this->ProductService = $productService;
    }

    public function lists()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'page' => ['integer', 'gt:0'],
                'size' => ['integer', 'gt:0', 'lte:20']
            ]);
            if($validator->fails())
                return $this->AppReturn(403,$validator->errors()->first());
            $this->ProductService->lists();
            return $this->AppReturn(
                $this->ProductService->_code,
                $this->ProductService->_msg,
                $this->ProductService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function add()
    {
        try{
            $validator = Validator::make(request()->post(), [
                'name' => ['required', 'between:2,50', 'alpha_dash'],
                'price' => ['required', 'gt:0', 'numeric'],
                'back_money' => ['required', 'gt:0', 'numeric'],
                'sort' => ['integer', 'gt:0'],
                'status' => ['required', Rule::in([0, 1])],
                'cover' => ['required', 'gt:0', 'integer'],
                'images' => ['required', 'array']
            ]);
            if($validator->fails())
                return $this->AppReturn(403,$validator->errors()->first());
            $this->ProductService->add();
            return $this->AppReturn(
                $this->ProductService->_code,
                $this->ProductService->_msg,
                $this->ProductService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function update()
    {
        try{
            $validator = Validator::make(request()->post(), [
                'product_id' => ['required', 'integer', 'gt:0'],
                'name' => ['required', 'between:2,50', 'alpha_dash'],
                'price' => ['required', 'gt:0', 'numeric'],
                'back_money' => ['required', 'gt:0', 'numeric'],
                'sort' => ['integer', 'gt:0'],
                'status' => ['required', Rule::in([0, 1])],
                'cover' => ['required', 'gt:0', 'integer'],
                'images' => ['required', 'array']
            ]);
            if($validator->fails())
                return $this->AppReturn(403,$validator->errors()->first());
            $this->ProductService->update();
            return $this->AppReturn(
                $this->ProductService->_code,
                $this->ProductService->_msg,
                $this->ProductService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function edit()
    {
        try{
            $validator = Validator::make(request()->post(), [
                'product_id' => ['required', 'integer', 'gt:0'],
                'field' => ['required', Rule::in(['sort', 'status'])]
            ]);
            if($validator->fails())
                return $this->AppReturn(403,$validator->errors()->first());
            $this->ProductService->edit();
            return $this->AppReturn(
                $this->ProductService->_code,
                $this->ProductService->_msg,
                $this->ProductService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function detail()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'product_id' => ['required', 'integer', 'gt:0']
            ]);
            if($validator->fails())
                return $this->AppReturn(403,$validator->errors()->first());
            $this->ProductService->detail();
            return $this->AppReturn(
                $this->ProductService->_code,
                $this->ProductService->_msg,
                $this->ProductService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

    public function del()
    {
        try{
            $validator = Validator::make(request()->input(), [
                'product_id' => ['required', 'integer', 'gt:0']
            ]);
            if($validator->fails())
                return $this->AppReturn(403,$validator->errors()->first());
            $this->ProductService->del();
            return $this->AppReturn(
                $this->ProductService->_code,
                $this->ProductService->_msg,
                $this->ProductService->_data
            );
        }catch(\Exception $e){
            $this->logError('adminerr', $e);
            return $this->AppReturn(501,$e->getMessage());
        }
    }

}
