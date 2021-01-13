<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Services\Api\ProductService;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make(request()->input(), [
            'page' => ['required', 'integer', 'gt:0'],
            'size' => ['required', 'integer', 'gte:1', 'lt:20']
        ]);
        if($validator->fails())
            return $this->AppReturn(403,$validator->errors()->first());
        $this->ProductService->lists();
        return $this->AppReturn(
            $this->ProductService->_code,
            $this->ProductService->_msg,
            $this->ProductService->_data
        );
    }

    public function detail()
    {
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
    }

}
